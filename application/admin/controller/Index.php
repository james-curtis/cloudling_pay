<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/1/29
 * Time: 21:11
 */

namespace app\admin\controller;

use app\admin\model\Config;
use app\index\controller\Base;
use app\index\model\LoginLog;
use app\index\model\Order;
use app\index\controller\Pay;
use app\index\model\User;
use think\Db;
use think\Session;
use think\Validate;

class Index extends Base
{
    protected function _initialize()
    {
        parent::_initialize();
        if (!$this->checkAdminLogin() && request() -> action() != 'login')
            return view('login');
    }

    public function index()
    {
        return view();
    }

    public function login()
    {
        if ($this->checkAdminLogin()) {
            $this->success('您已登录', 'index');
        }

        if (input('post.__token__')) {
            $user = input('post.user');
            $pwd = input('post.pwd');
            $validate = new Validate([
                'user|用户名' => 'require|token',
                'pwd|密码' => 'require'
            ]);
            if (!$validate->check(input('post.'))) {
                $this->error($validate->getError());
            } elseif ($user == $this->set['admin_user'] && $pwd == $this->set['admin_pwd']) {
                Session::set('admin', 1);
                Session::set('admin_login_time', time());
                $log = new LoginLog();
                $r = $log->save([
                    'type' => 1,
                ]);
                if ($r) {
                    $this->success('登录成功', 'index');
                } else {
                    $this->error('记录失败');
                }
            } else {
                $this->error('用户名或密码错误');
            }
        }

        return view();
    }

    public function loginOut()
    {
        Session::delete(['admin', 'admin_login_time']);
        $this->success('注销登录成功', 'login');
    }

    /**
     * 检查后台登录情况
     * @return bool
     */
    protected function checkAdminLogin()
    {
        if (Session::has('admin') && Session::get('admin_login_time') > time() - 1 * 24 * 60 * 60) {
            return true;
        } else {
            return false;
        }
    }

    public function order()
    {
        $mod = input('post.mod');
        $value = input('post.value');
        $column = input('post.column');

        //分页参数
        $pagesize = 20;
        $page = input('get.page') ? input('get.page') : 1;

        if ($mod == 'search') {
            if (!ajax_check_token(input('post.__token__'))) {
                $this->error('请求异常');
            }
            if ($column == 'name') {
                $order = Order::scope('likeName', $value);
                $numrows = Order::where('name', 'like', '%' . $value . '%')->count();
            } else {
                $order = Order::scope('column', $column, $value);
                $numrows = Order::where($column, $value)->count();
            }
            $data = $order->page($page, $pagesize)->order('trade_no desc')->select();
            $con = '<div class="text-center"><div class="panel panel-default"><div class="panel-heading">包含 ' . $value . ' 的共有 <b>' . $numrows . '</b> 条订单</div></di></div>';
            $link = '&my=search&column=' . $column . '&value=' . $value;
        } else {
            $order = Order::count();
            $data = $order->page($page, $pagesize)->order('trade_no desc')->select();
            $numrows = $order->count();
            $con = '<div class="text-center"><div class="panel panel-default"><div class="panel-heading">共有 <b>' . $numrows . '</b> 条订单</div></div></div>';
            $link = '';

        }

        return view('', ['con' => $con, 'data' => $data, 'order' => new Order(), 'page' => $this->makePage($page, $numrows, url('order'), $link)]);
    }

    public function loginLog()
    {
        //分页参数
        $pagesize = 20;
        $page = input('get.page') ? input('get.page') : 1;

        $log = new LoginLog();
        $total = $log -> count();
        $data = $log -> page($page,$pagesize) -> select();
        return view('',['data' => $data,'page' => $this->makePage($page,$total,url('loginLog')),'total' => $total]);
    }

    /**
     * 构造分页HTML
     * @param int $page 单曲页数
     * @param int $data_total 数据总数
     * @param string $link 跳转链接
     * @param string $args 额外参数
     * @param int $page_size 每页数量
     * @return string 分页HTML
     */
    protected function makePage($page, $data_total, $link = '', $args = '',$page_size=20)
    {
        $html = '<ul class="pagination">';
        $first = 1;
        $prev = $page - 1;
        $next = $page + 1;
        $pages = intval($data_total / $page_size);
        if ($data_total % $page_size) {
            $pages++;
        }
        $last = $pages;
        if ($page > 1) {
            $html .= '<li><a href="' . $link . '?page=' . $first . $args . '">首页</a></li>';
            $html .= '<li><a href="' . $link . '?page=' . $prev . $args . '">&laquo;</a></li>';
        } else {
            $html .= '<li class="disabled"><a>首页</a></li>';
            $html .= '<li class="disabled"><a>&laquo;</a></li>';
        }

        for ($i = 1; $i < $page; $i++)
            $html .= '<li><a href="' . $link . '?page=' . $i . $args . '">' . $i . '</a></li>';

        $html .=  '<li class="disabled"><a>' . $page . '</a></li>';

        for ($i = $page + 1; $i <= $pages; $i++)
            $html .= '<li><a href="' . $link . '?page=' . $i . $args . '">' . $i . '</a></li>';

        if ($page < $pages) {
            $html .= '<li><a href="' . $link . '?page=' . $next . $args . '">&raquo;</a></li>';
            $html .= '<li><a href="' . $link . '?page=' . $last . $args . '">尾页</a></li>';
        } else {
            $html .= '<li class="disabled"><a>&raquo;</a></li>';
            $html .= '<li class="disabled"><a>尾页</a></li>';
        }
        $html .= '</ul>';
        return $html;
    }

    public function traderList()
    {
        $user_obj = new User();
        $user_total = $user_obj -> count();
        $user_obj = new User();

        //分页参数
        $page_size = 20;
        $page = input('get.page')?input('get.page'):1;

        if (input('post.mod') == 'search')
        {
            if (!ajax_check_token(input('post.__token__')))$this->error('请求异常');
            if (input('post.column') == 'url')
            {
                $user_total = $user_obj -> where(input('post.column'),'like','%'.input('post.value').'%') -> count();
                $user_obj = $user_obj -> where(input('post.column'),'like','%'.input('post.value').'%');
            }
            else
            {
                $user_total = $user_obj -> where(input('post.column'),input('post.value')) -> count();
                $user_obj = $user_obj -> where(input('post.column'),input('post.value'));
            }
        }
        $data = $user_obj -> page($page,$page_size) -> select();

        return view('',['user_total' => $user_total,'data' => $data,'page' => $this->makePage($page,$user_total)]);
    }

    public function addUser()
    {
        if (input('post.__token__'))
        {
            if (!ajax_check_token(input('post.__token__')))$this->error('非法请求');
            $user = new User();
            $result = true;//halt(input('post.is_trader'));
            if ($user -> allowField(true) -> save(input('post.')))
            {
                if (input('post.is_trader') == 1)
                {
                    $user_pid = new User();
                    $before_pid = $user_pid -> where('is_trader',1) -> order('pid desc') -> value('pid');
                    if (!$user -> isUpdate() -> save(['key' => random(32),'level' => 1,'pid' => $before_pid+1,'is_trader' => 1]))$result = false;
                }
            }
            else $result = false;
            if ($result)
            {
                $this->success('添加成功');
            }
            else
                $this->error('添加失败');
        }
        return view();
    }

    public function editUser()
    {
        $user = User::get(input('get.uid'));
        if (input('post.__token__'))
        {
            if (!ajax_check_token(input('post.__token__')))$this->error('非法请求');
            $result = true;
            //顺序不能乱
            $is_trader = $user -> is_trader;
            if (!$user -> isUpdate() -> allowField(true) -> save(input('post.')))$result = false;

            if (input('post.resetPwd'))
            {
                if (!$user -> isUpdate() -> save(['pwd' => '123456']))$result = false;
            }
            if (input('post.resetKey'))
            {
                if (!$user -> isUpdate() -> save(['key' => random(32)]))$result = false;
            }
            if (input('post.is_trader'))
            {
                if ($is_trader != 1)
                {
                    $user_pid = new User();
                    $before_pid = $user_pid -> where('is_trader',1) -> order('pid desc') -> value('pid');
                    if (!$user -> isUpdate() -> save(['key' => random(32),'level' => 1,'pid' => $before_pid+1]))$result = false;
                }
            }


            if ($result)
            {
                $this->success('修改成功');
            }
            else
            {
                $this->error('修改失败');
            }

        }
        return view('',['user' => $user]);
    }

    public function collaboratorList()//合作商户
    {

    }

    public function deleteUser()
    {
        if (input('get.uid'))
        {
            if (User::destroy(['uid' => input('get.uid')]))
            {
                $this->success('删除成功');
            }
            else
            {
                $this->error('删除失败');
            }
        }
    }

}
























