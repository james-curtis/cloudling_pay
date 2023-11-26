<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/2/2
 * Time: 14:44
 */

namespace app\index\model;


use think\Model;

class RegCode extends Model
{
    protected $createTime = 'time';

    protected $updateTime = false;

    protected $insert = [
        'ip',
        'verifytype',
    ];

    protected function setIpAttr()
    {
        return request() -> ip();
    }

    protected function setVerifytypeAttr()
    {
        return get_config()['reg_verify_type'];
    }

    /**
     * 通过email搜索两次发信间隔小于规定时间的注册码
     * @param $q
     * @param $e
     * @return mixed
     */
    protected function scopeTwice_forbidden_by_email($q,$e)
    {
        return $q->where('email', $e)->whereTime('time', '>', time() - intval(get_config()['web_sendemail_interval']));
    }

    /**
     * 通过email搜索当天被封锁的注册码
     * @param $q
     * @param $e
     * @return mixed
     */
    protected function scopeForbidden_by_email($q,$e)
    {
        return $q->where('email', $e)->whereTime('time', 'today');
    }

    /**
     * 通过IP搜索当天被封锁的注册码
     * @param $q
     * @return mixed
     */
    protected function scopeForbidden_by_ip($q)
    {
        return $q->where('ip', request()->ip())->whereTime('time', 'today');
    }

}









