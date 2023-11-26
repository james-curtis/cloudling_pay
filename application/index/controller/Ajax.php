<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/2/2
 * Time: 13:13
 */

namespace app\index\controller;


use app\index\model\FindCode;
use app\index\model\LoginLog;
use app\index\model\RegCode;
use think\Log;
use think\Session;
use app\index\model\User;
use base\Email;

class Ajax extends Base
{
    /**
     * 初始化
     */
    protected function _initialize()
    {
        parent::_initialize();
        $expect = [
            'sendregcode',
            'sendregpost',
        ];
        if (!in_array(strtolower($this->request->action()),$expect)) {
            $this->checkLogin();
        }
    }

    /**
     * 异步补加用户登录地理位置
     * @throws \think\exception\DbException
     */
    public function addLoginLogId()
    {
        $id = Session::get('login_log_id');
        if (empty($id)) return;
        $login_log = LoginLog::get($id);
        if ($login_log->save(['city' => request()->ip()])) {
            Session::delete('login_log_id');
        }
        return;
    }

    /**
     * 发送验证码
     * @return string
     * @throws \think\Exception
     */
    public function sendRegCode()
    {
        $code = input('post.code');//return $this->autoOut(['msg' => input('post.__token__').'|'.Session::get('__token__')]);
        $email = input('post.email');
        $phone = input('post.phone');
        $__token__ = input('post.__token__');
        if ($this->set['reg_open'] != 'open') {
            $this->error('未开放商户申请');
        }
        if (!captcha_check($code) && $this->set['web_open_reg_captcha'] == 'open') {
            $this->error('验证码错误');
        }
        if (!ajax_check_token(input('post.__token__')))
        {
            $this->error('系统检测到恶意请求，已自动屏蔽');
        }
        $this->set['reg_verify_type'] == 1 ? $validate = 'phone' : $validate = 'email';

        $user = new User();
        if ($this->set['reg_verify_type'] == 0) {

            if ($user->where('email', $email)->count() != 0) {
                $this->error('该邮箱已经注册过账号，如需找回账号，请返回登录页面点击找回账号');
            }
            if (RegCode::scope('twice_forbidden_by_email',$email) ->count() > 0) {
                $this->error('两次发送邮件之间需要相隔' . $this->set['web_sendemail_interval'] . '秒！');
            }
            if (RegCode::scope('forbidden_by_email',$email)->count() > 6) {
                $this->error('今天该邮箱发送次数过多，请更换邮箱，或明天再来注册');
            }
            if (RegCode::scope('forbidden_by_ip')->count() > 10) {
                $this->error('你今天发送次数过多，已被禁止注册');
            }


            $reg_code = new RegCode();
            $sub = $this->set['web_title'] . ' - 验证码获取';
            $code = rand(1111111, 9999999);
            $msg = '您的验证码是：' . $code;
//            $result = send_mail($email, $sub, $msg);
            $e = new Email();
            $result = $e -> SendHtml([
                'email' => $email,
                'content' => $msg,
                'title' => $sub
            ]);
            //发信成功
            if ($result === true) {
                //记录
                if ($reg_code -> validate('Code.'.$validate) -> save(['code' => $code,'email' => $email])) {
                    Session::set('send_id', $reg_code['id']);
                    return $this->autoOut(['code' => 1, 'msg' => 'succ']);
                } else {
                    $this->error($reg_code->getError());
                }
            } else {
                $this->error('邮件发送失败:'.$e -> error);
            }
        }
    }

    public function sendRegPost()
    {
        if (Session::has('reg_post'))
        {
            $emailer = new Email();
            $r = $emailer -> SendHtml([
                'email' => Session::get('reg_post.email'),
                'content' => Session::get('reg_post.content'),
                'title' => Session::get('reg_post.title')
            ]);
            if ($r)
            {
                Session::delete('reg_post');
            }
        }
    }

    /**
     * ajax获取登录记录
     * @return string
     * @throws \think\Exception
     */
    public function loginLog()
    {
        $start = input('post.start');//数据开始位置
        $one_page_length = input('post.length');//每页数据个数
        $search = input('search.value');//搜索值
        $current_page = ceil(($start + 1) / $one_page_length);//计算当前页
        switch (input('post.order.0.column')) {
            default:
            case 0:
                $order_col = 'id';
                break;

            case 1:
                $order_col = 'date';
                break;

            case 2:
                $order_col = 'city';
                break;

            case 3:
                $order_col = 'ip';
                break;
        }
        $order_way = input('order.0.dir') == 'asc' ? 'asc' : 'desc';//排序方式(asc:升序,desc:降序)

        $log_db = db('login_log');
        $data = [];
        $log_obj = [];
        $log_obj['draw'] = input('post.draw');
        //数据库里总共记录数
        $log_obj['recordsTotal'] = $log_db->where('uid', Session::get('uid'))->count();
        //过滤后的记录数
        if (empty($search)) {
            $log_obj['recordsFiltered'] = $log_obj['recordsTotal'];
            $logs = $log_db
                ->where('uid', Session::get('uid'))
                ->page($current_page, $one_page_length)
                ->order($order_col . ' ' . $order_way)
                ->select();
        } else {
//            $log_obj['recordsFiltered'] = $log_db -> where('id|date|city|ip','like','%'.$search.'%') -> where('uid',Session::get('uid')) -> count();

            $where = '';
            //含中文
            if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $search) > 0) {
                $where = 'city';
            } else {
                $where = 'id|date|ip|city';
            }
            $logs = $log_db
                ->where('uid', Session::get('uid'))
                ->where($where, 'like', '%' . $search . '%')
                ->page($current_page, $one_page_length)
                ->order($order_col . ' ' . $order_way)
                ->select();

            $log_obj['recordsFiltered'] = count($logs);
        }

        //组装数据
        foreach ($logs as $log) {
            $data[] = [
                $log['id'],
                $log['date'],
                $log['city'],
                $log['ip']
            ];
        }
        $log_obj['data'] = [];

        $log_obj = (object)$log_obj;

        $log_obj->data = $data;


        return $this->autoOut($log_obj);
    }

    public function orderLog()
    {
        $trader = User::get(Session::get('uid'));
        $pid = $trader->pid;
        if (empty($pid) || empty($trader->is_trader)) $this->error('您目前还不是商户请先申请成为商户', 'applyTrader');


        $start = input('post.start');//数据开始位置
        $one_page_length = input('post.length');//每页数据个数
        $search = input('search.value');//搜索值
        $current_page = ceil(($start + 1) / $one_page_length);//计算当前页
        switch (input('post.order.0.column')) {
            default:
            case 0:
                $order_col = 'trade_no';//交易号
                break;

            case 1:
                $order_col = 'out_trade_no';//商户订单号
                break;

            case 2:
                $order_col = 'name';
                break;

            case 3:
                $order_col = 'money';
                break;

            case 4:
                $order_col = 'type';
                break;

            case 5:
                $order_col = 'createtime';
                break;

            case 6:
                $order_col = 'endtime';
                break;

            case 7:
                $order_col = 'ip';
                break;

            case 8:
                $order_col = 'status';
                break;
        }
        $order_way = input('order.0.dir') == 'asc' ? 'asc' : 'desc';//排序方式(asc:升序,desc:降序)

        $log_db = db('order');
        $data = [];
        $log_obj = [];
        $log_obj['draw'] = input('post.draw');

        //数据库里总共记录数
        $log_obj['recordsTotal'] = $log_db->where('pid', $pid)->count();

        //过滤后的记录数
        if (empty($search)) {
            $log_obj['recordsFiltered'] = $log_obj['recordsTotal'];
            $logs = $log_db
                ->where('pid', $pid)
                ->page($current_page, $one_page_length)
                ->order($order_col . ' ' . $order_way)
                ->select();
        } else {
            //自动检索
            $where = 'trade_no|out_trade_no|createtime|endtime';

            //检索状态
            if ($search == '已支付') {
                $search = 1;
                $where = 'status';
            } elseif ($search == '未支付') {
                $search = 0;
                $where = 'status';
            } else {

                //检索类型
                if ($search == '支付宝') {
                    $where = 'type';
                    $search = 'alipay';
                } elseif ($search == '微信') {
                    $where = 'type';
                    $search = 'wx';
                } elseif (strtoupper($search) == 'QQ') {
                    $where = 'type';
                    $search = 'qq';
                } else {
                    //检索金额
                    if (stripos($search, '￥') !== false) {
                        $where = 'money';
                        $search = str_replace('￥', '', $search);
                    } else {
                        //检索IP
                        if (preg_match('/(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)/', $search) !== 0) {
                            $where = 'ip';
                        }
                        else
                        {
                            //检索商品名
                            if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $search) > 0)//含有中文
                            {
                                $where = 'name';
                            }
                        }
                    }
                }
            }


            $logs = $log_db
                ->where($where, 'like', '%' . $search . '%')
                ->where('pid', $pid)
                ->page($current_page, $one_page_length)
                ->order($order_col . ' ' . $order_way)
                ->select();
            $log_obj['recordsFiltered'] = count($logs);
        }

        //组装数据
        foreach ($logs as $log) {
            $data[] = [
                $log['trade_no'],
                $log['out_trade_no'],
                $log['name'],
                '￥'.$log['money'],
                $log['type'] == 'alipay' ? '支付宝' : ($log['type'] == 'qq' ? 'QQ' : ($log['type'] == 'wx' ? '微信' : '未知')),
                date('Y-m-d',strtotime($log['createtime'])).'<br>'.date('H:i:s',strtotime($log['createtime'])),
                date('Y-m-d',strtotime($log['endtime'])).'<br>'.date('H:i:s',strtotime($log['endtime'])),
//                $log['ip'],
                '<span style="color:'.($log['status']==1?'green">已支付':'red">未支付').'</span>',
                '<button onclick="window.open(\'' . url('orderLog') . '\')" type="button" class="btn btn-outline-success btn-sm"><i class="fa fa-magic"></i>&nbsp;&nbsp;补单</button>',//'.$log['notify_url'].'?trade_no='.$log['trade_no'].'&out_trade_no='.$log['out_trade_no'].'&name='.urlencode($log['name']).'&
            ];
        }
        $log_obj['data'] = [];

        $log_obj = (object)$log_obj;

        $log_obj->data = $data;


        return $this->autoOut($log_obj);
    }

    /*public function sendFindCode()
    {
        $code = input('post.code');
        $email = input('post.email');
        $phone = input('post.phone');

        //先验证格式
        if (!empty($email))
        {
            if (!$this->validate($email,'FindCode.email'))
            {
                $this->error('邮箱格式错误');
            }
        }
        else
        {
            if (!$this->validate($phone,'FindCode.phone'))
            {
                $this->error('手机号不正确');
            }
        }
        if (captcha_check($code))
        {
            $this->error('验证码错误');
        }
        if (Session::get('find_time') > time() - intval($this->set['web_sendemail_interval']))
        {
            $this->error('两次发送邮件之间需要相隔' . $this->set['web_sendemail_interval'] . '秒！');
        }

        $sub = $this->set['web_title'] . ' - 验证码获取';
        $code = rand(1111111, 9999999);
        $msg = '您的验证码是：' . $code;

        if (!empty($email))
        {
            $result = send_mail($email, $sub, $msg);
            if ($result)
            {
                $find_code = new FindCode([
                    'email' => $email,
                    'code' => $code
                ]);
                $result = $find_code -> validate('FindCode.email') -> save();
                if ($result)
                {
                    Session::set('find_time',time());
                    Session::set('find_id',$find_code['id']);
                    return $this->autoOut(['code' => 1,'msg' => '发送成功']);
                }
                else
                {
                    return $this->autoOut(['code' => 0,'msg' => '记录失败']);
                }
            }
            else
            {
                return $this->autoOut(['code' => 0,'msg' => '发送失败']);
            }
        }
        else
        {
            //TODO:发送短信
        }

    }*/
}