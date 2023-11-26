<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/2/5
 * Time: 23:28
 */

namespace app\index\controller;

use app\index\model\Collaborator;
use app\index\model\User;

class Api extends Base
{
    protected $act;

    protected $url;

    protected $auth_code;

    protected $token;

    protected function _initialize()
    {
        parent::_initialize();
        $this->act = input('get.act') ? daddslashes(input('get.act')) : null;
        $this->url = daddslashes(input('get.url'));
        if (request() -> isSsl())
        {
            $this->url = 'https://'.$this->url.'/';
        }
        $this->auth_code = daddslashes(input('get.authcode'));
        $this->token = daddslashes(input('get.token'));
    }

    /**
     * 空操作
     * 防止输出不友好信息
     */
    public function _empty()
    {
        return $this->autoOut(['code' => -1,'msg' => '空操作']);
    }

    public function add()
    {
        $user = new User();
        $type = 1;
        $pid_last = $user -> where(['is_trader' => 1,'delete_time' => null]) -> order('pid desc') -> value('pid');
        if ($user -> isUpdate(false) -> save(['key' => random(32),'url' => $this->url,'pid' => $pid_last+1,'level' => 1]))
        {
            return $this->autoOut(['code' => 1,'msg' => '添加支付商户成功！','pid' => $user -> pid,'key' => $user -> key,'type' => $type]);
        }
        else
        {
            return $this->autoOut(['code' => -1,'msg' => '添加支付商户失败！']);
        }
    }

    public function apply()
    {//$this->request -> host()
        $row = Collaborator::get(['token' => $this->token]);
        if (!empty($row['id']) && $row['active'] == 1)
        {
            $type = 0;
            $username = input('get.username')?input('get.username'):rand(111111111,999999999);
            $user = new User([
                'key' => random(32),
                'cid' => $row -> cid,
                'url' => $this->url,
                'username' => $username,
                'pwd' => '123456'
            ]);
            if ($user -> save())
            {
                //TODO:update用户为商户
//                return $this->autoOut(['code' => 1,'key' => $user -> key,'pid' => ])
            }
        }
        else
        {
            return $this->autoOut(['code' => -1,'msg' => 'TOKEN ERROR']);
        }
    }
}