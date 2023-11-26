<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/1/31
 * Time: 18:25
 */

namespace app\index\model;


use think\Model;

class LoginLog extends Model
{
    protected $createTime = 'date';

    protected $updateTime = false;

    //插入时自动完成
    protected $insert = ['ip'];

    protected function setCityAttr($ip)
    {
        return get_city_by_ip($ip);
    }

    protected function setIpAttr()
    {
        return request() -> ip();
    }

    protected function scopeUid($query,$uid,$limit = 10,$by = 'desc')
    {
        $query -> where('uid',$uid) -> limit($limit) -> order('id '.$by);
    }

}