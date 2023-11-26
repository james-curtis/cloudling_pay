<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/1/29
 * Time: 16:27
 */

namespace app\index\controller;


use think\Controller;
use think\Exception;
use think\Request;
use think\Session;
use app\index\model\User as UserModel;

class Base extends Controller
{
    /**
     * 系统设置
     * @var array
     */
    protected $set = [];

    /**
     * 模板特殊位置
     * @var array
     */
    protected $expect = [
        'index' => [
            'User|console' => ['login' => 'user','reg' => 'user','find' => 'user'],
            'Index|portal',
        ],
        'admin|admin',
    ];

    /**
     * 检查安装情况
     */
    protected function checkInstall()
    {
        if (empty($this->set['current_template']))
        {
            $this -> error('请先安装程序','install/index/index');
        }
    }

    /**
     * Base constructor.
     * @param Request|null $request
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function __construct(Request $request = null)
    {
        $this->set = get_config();
        $this->checkInstall();

        $public = $this->loadTpl();
        parent::__construct($request);

        //一次性解决所有模板资源问题
        $this -> view -> replace('__PUBLIC__',$public);

        $this->assign('set',$this->set);
        $uid = Session::get('uid');
        if (!empty($uid))
        {
            $this->assign('user',UserModel::get($uid));
        }
    }


    /**
     * 自动加载模板
     * @return string
     * @throws Exception
     */
    protected function loadTpl()
    {
        $base_path = ROOT_PATH.'template'.DS.$this->set['current_template'].DS;
        $add_path = '';
        //开始判断模块
        foreach ($this->expect as $module_base => $controllers) {
            if (preg_match('/\d{1,}/',$module_base) > 0)
            {
                $temp = $module_base;
                $module_base = $controllers;
                $controllers = $temp;
            }

            if (strexists($module_base,'|'))
            {
                $ex = explode('|',$module_base);
                $module = $ex[0];//模块名
                $module_expect = $ex[1];//模块模板目录
                if (request() -> module() == $module)
                {
                    //开始判断控制器
                    if (empty($controllers))//没有写特殊控制器
                    {
                        $add_path = $module_expect;
                        break;//匹配成功
                    }
                    else
                    {
                        /*
                         * //只有一个特殊控制器
                         * 写法一: 控制器名|模板目录
                         * //写法二: [控制器名 => 模板目录]
                         */
                        if (!is_array($controllers))
                        {
                            if (strexists($controllers,'|'))//写法一
                            {
                                $ex = explode('|',$controllers);
                                $controller = $ex[0];//控制器名
                                $controller_expect = $ex[1];//控制器模板目录
                                if (request() -> controller() == $controller)
                                {
                                    $add_path = $controller_expect;
                                    break;//匹配成功
                                }
                                else//匹配完了，就一个嘛
                                {
                                    continue;
                                }
                            }
                            else
                            {
                                throw new Exception('模板配置错误');
                            }
                        }
                        /*
                         * 如果写成数组形式，则有一个或者多个特殊控制器
                         * 写法一: '控制器名|模板目录'
                         * 写法二: '控制器名' => '模板目录'
                         * 写法三: '控制器名' => [
                         *                          '操作名|模板目录',
                         *                          '操作名' => '模板目录',
                         *                          ...
                         *                       ]
                         * 写法四: '控制器名|模板目录' =>
                         *                      [
                         *                          '操作名|模板目录',
                         *                          '操作名' => '模板目录',
                         *                          ...
                         *                       ]
                         * 写法五: '控制器名|模板目录' => '操作名|模板目录'
                         */
                        else
                        {
                            foreach ($controllers as $controller => $actions) {
                                if (preg_match('/\d{1,}/',$controller) > 0)
                                {
                                    $temp = $controller;
                                    $controller = $actions;
                                    $actions = $temp;
                                }
                                //写法一、四、五
                                if (strexists($controller,'|'))
                                {
                                    $ex = explode('|',$controller);
                                    $controller = $ex[0];
                                    $controller_expect = $ex[1];
                                    if (request() -> controller() == $controller)
                                    {
                                        $add_path = $controller_expect;
                                        //开始判断操作
                                        if (!empty($actions))//不为空，可能为string或者array。'操作名|模板目录'；或者'操作名' => '模板目录'
                                        {
                                            if (!is_array($actions))//为string
                                            {//写法五
                                                if (strexists($actions,'|'))
                                                {
                                                    $ex = explode('|',$actions);
                                                    $action = $ex[0];
                                                    $action_expect = $ex[1];
                                                    if (request() -> action() == $action)
                                                    {
                                                        $add_path = $action_expect;
                                                        //匹配成功
                                                        break;
                                                    }
                                                    else
                                                    {
                                                        continue;
                                                    }
                                                }
                                                else
                                                {
                                                    //写法有误
                                                    throw  new Exception('模板设置错误');
                                                }
                                            }
                                            else//可能为数组或者空
                                            {//写法四
                                                if (is_array($actions))//为数组
                                                {
                                                    foreach ($actions as $action => $action_expect) {
                                                        if (preg_match('/\d{1,}/',$action) > 0)
                                                        {
                                                            $temp = $action;
                                                            $action = $action_expect;
                                                            $action_expect = $temp;
                                                        }
                                                        if (!strexists($action,'|'))//'操作名' => '模板目录'
                                                        {
                                                            if (request() -> action() == $action)
                                                            {
                                                                $add_path = $action_expect;
                                                                //匹配成功
                                                                break;
                                                            }
                                                            else
                                                            {
                                                                continue;
                                                            }
                                                        }
                                                        else//'操作名|模板目录'
                                                        {
                                                            $ex = explode('|',$action);
                                                            $action = $ex[0];
                                                            $action_expect = $ex[1];
                                                            if (request() -> action() == $action)
                                                            {
                                                                $add_path = $action_expect;
                                                                //匹配成功
                                                                break;
                                                            }
                                                            else
                                                            {
                                                                continue;
                                                            }
                                                        }
                                                    }
                                                }
                                                else
                                                {
                                                    throw new Exception('模板设置错误');
                                                }
                                            }
                                        }
                                        else
                                        {
                                            //写法一
                                            break;//匹配成功
                                        }
                                    }
                                    else
                                    {
                                        continue;
                                    }
                                }
                                else//可能为写法二、三
                                {
                                    if (!empty($actions))
                                    {
                                        if (!is_array($actions))//写法二
                                        {
                                            if (request() -> controller() == $controller)
                                            {
                                                $add_path = $actions;
                                                //匹配成功
                                                break;
                                            }
                                            else
                                            {
                                                continue;
                                            }
                                        }
                                        else//写法三
                                        {
                                            foreach ($actions as $action => $action_expect) {
                                                if (preg_match('/\d{1,}/',$action) > 0)
                                                {
                                                    $temp = $action;
                                                    $action = $action_expect;
                                                    $action_expect = $temp;
                                                }
                                                if (!strexists($action,'|'))//'操作名' => '模板目录'
                                                {
                                                    if (request() -> action() == $action)
                                                    {
                                                        $add_path = $action_expect;
                                                        //匹配成功
                                                        break;
                                                    }
                                                    else
                                                    {
                                                        continue;
                                                    }
                                                }
                                                else
                                                {
                                                    $ex = explode('|',$action);
                                                    $action = $ex[0];
                                                    $action_expect = $ex[1];
                                                    if (request() -> action() == $action)
                                                    {
                                                        $add_path = $action_expect;
                                                        //匹配成功
                                                        break;
                                                    }
                                                    else
                                                    {
                                                        continue;
                                                    }
                                                }

                                            }
                                        }
                                    }
                                    else
                                    {
                                        throw new Exception('模板设置错误');
                                    }
                                }
                            }
                        }
                    }
                }
                else
                {
                    continue;
                }
            }
            elseif (!empty($controllers))
            {
                if (request() -> module() == $module_base)
                {
                    //开始判断控制器
                    if (empty($controllers))//没有写特殊控制器
                    {
                        throw new Exception('模板设置错误');
                    }
                    else
                    {
                        /*
                         * //只有一个特殊控制器
                         * 写法一: 控制器名|模板目录
                         * //写法二: [控制器名 => 模板目录]
                         */
                        if (!is_array($controllers))
                        {
                            if (strexists($controllers,'|'))//写法一
                            {
                                $ex = explode('|',$controllers);
                                $controller = $ex[0];//控制器名
                                $controller_expect = $ex[1];//控制器模板目录
                                if (request() -> controller() == $controller)
                                {
                                    $add_path = $controller_expect;
                                    break;//匹配成功
                                }
                                else//匹配完了，就一个嘛
                                {
                                    continue;
                                }
                            }
                            else
                            {
                                throw new Exception('模板配置错误');
                            }
                        }
                        /*
                         * 如果写成数组形式，则有一个或者多个特殊控制器
                         * 写法一: '控制器名|模板目录'
                         * 写法二: '控制器名' => '模板目录'
                         * 写法三: '控制器名' => [
                         *                          '操作名|模板目录',
                         *                          '操作名' => '模板目录',
                         *                          ...
                         *                       ]
                         * 写法四: '控制器名|模板目录' =>
                         *                      [
                         *                          '操作名|模板目录',
                         *                          '操作名' => '模板目录',
                         *                          ...
                         *                       ]
                         * 写法五: '控制器名|模板目录' => '操作名|模板目录'
                         */
                        else
                        {
                            foreach ($controllers as $controller => $actions) {
                                if (preg_match('/\d{1,}/',$controller) > 0)
                                {
                                    $temp = $controller;
                                    $controller = $actions;
                                    $actions = $temp;
                                }
                                //写法一、四、五
                                if (strexists($controller,'|'))
                                {
                                    $ex = explode('|',$controller);
                                    $controller = $ex[0];
                                    $controller_expect = $ex[1];
                                    if (request() -> controller() == $controller)
                                    {
                                        $add_path = $controller_expect;
                                        //开始判断操作
                                        if (!empty($actions))//不为空，可能为string或者array。'操作名|模板目录'；或者'操作名' => '模板目录'
                                        {
                                            if (!is_array($actions))//为string
                                            {//写法五
                                                if (strexists($actions,'|'))
                                                {
                                                    $ex = explode('|',$actions);
                                                    $action = $ex[0];
                                                    $action_expect = $ex[1];
                                                    if (request() -> action() == $action)
                                                    {
                                                        $add_path = $action_expect;
                                                        //匹配成功
                                                        break;
                                                    }
                                                    else
                                                    {
                                                        continue;
                                                    }
                                                }
                                                else
                                                {
                                                    //写法有误
                                                    throw  new Exception('模板设置错误');
                                                }
                                            }
                                            else//可能为数组或者空
                                            {//写法四
                                                if (is_array($actions))//为数组
                                                {
                                                    foreach ($actions as $action => $action_expect) {
                                                        if (preg_match('/\d{1,}/',$action) > 0)
                                                        {
                                                            $temp = $action;
                                                            $action = $action_expect;
                                                            $action_expect = $temp;
                                                        }
                                                        if (!strexists($action,'|'))//'操作名' => '模板目录'
                                                        {
                                                            if (request() -> action() == $action)
                                                            {
                                                                $add_path = $action_expect;
                                                                //匹配成功
                                                                break;
                                                            }
                                                            else
                                                            {
                                                                continue;
                                                            }
                                                        }
                                                        else//'操作名|模板目录'
                                                        {
                                                            $ex = explode('|',$action);
                                                            $action = $ex[0];
                                                            $action_expect = $ex[1];
                                                            if (request() -> action() == $action)
                                                            {
                                                                $add_path = $action_expect;
                                                                //匹配成功
                                                                break;
                                                            }
                                                            else
                                                            {
                                                                continue;
                                                            }
                                                        }
                                                    }
                                                }
                                                else
                                                {
                                                    throw new Exception('模板设置错误');
                                                }
                                            }
                                        }
                                        else
                                        {
                                            //写法一
                                            break;//匹配成功
                                        }
                                    }
                                    else
                                    {
                                        continue;
                                    }
                                }
                                else//可能为写法二、三
                                {
                                    if (!empty($actions))
                                    {
                                        if (!is_array($actions))//写法二
                                        {
                                            if (request() -> controller() == $controller)
                                            {
                                                $add_path = $actions;
                                                //匹配成功
                                                break;
                                            }
                                            else
                                            {
                                                continue;
                                            }
                                        }
                                        else//写法三
                                        {
                                            foreach ($actions as $action => $action_expect) {
                                                if (preg_match('/\d{1,}/',$action) > 0)
                                                {
                                                    $temp = $action;
                                                    $action = $action_expect;
                                                    $action_expect = $temp;
                                                }
                                                if (!strexists($action,'|'))//'操作名' => '模板目录'
                                                {
                                                    if (request() -> action() == $action)
                                                    {
                                                        $add_path = $action_expect;
                                                        //匹配成功
                                                        break;
                                                    }
                                                    else
                                                    {
                                                        continue;
                                                    }
                                                }
                                                else
                                                {
                                                    $ex = explode('|',$action);
                                                    $action = $ex[0];
                                                    $action_expect = $ex[1];
                                                    if (request() -> action() == $action)
                                                    {
                                                        $add_path = $action_expect;
                                                        //匹配成功
                                                        break;
                                                    }
                                                    else
                                                    {
                                                        continue;
                                                    }
                                                }

                                            }
                                        }
                                    }
                                    else
                                    {
                                        throw new Exception('模板设置错误');
                                    }
                                }
                            }
                        }
                    }
                }
                else
                {
                    continue;
                }
            }
            else
            {
                throw new Exception('模板设置错误');
            }
        }

        config('template.view_path',$base_path.(empty($add_path)?'':$add_path.DS));
        $public = '/template/'.$this->set['current_template'].($add_path==''?'':'/'.$add_path);
        return $public;
    }

    /**
     * 检查登录情况
     * @return bool
     * @throws \think\exception\DbException
     */
    protected function checkLogin()
    {
        $user = \app\index\model\User::get(Session::get('uid'));
        if (!$user) {
            $this->error('请先登录', 'login');
        }
        if (time() - Session::get('login_time') > 1 * 24 * 60 * 60) {
            $this->error('您的登录信息已过期，请重新登录', 'login');
        }
        return true;
    }

    /**
     * 自动输出内容
     * @param $msg
     * @return string
     */
    protected function autoOut($msg)
    {
        $default_ajax_return = config('default_ajax_return');
        if ($this->request -> isAjax())
        {
            return $default_ajax_return($msg);
        }
        else
        {
            if (is_array($msg))
            {
                return dump($msg);
            }
            else
            {
                return $msg;
            }
        }
    }

}