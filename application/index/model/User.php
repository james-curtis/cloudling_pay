<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/1/29
 * Time: 21:28
 */

namespace app\index\model;


use think\Model;
use traits\model\SoftDelete;

class User extends Model
{
    //使用软删除
    use SoftDelete;

    protected $insert = [
        'is_trader' => 0,
        'active' => 1,
    ];

    protected $updateTime = false;

    protected $createTime = 'regtime';

    protected function setPwdAttr($pwd)
    {
        return md5($pwd);
    }

    protected function scopeEmail($query,$email)
    {
        return $query -> where('email',$email);
    }

    protected function scopePhone($query,$phone)
    {
        return $query -> where('phone',$phone) -> where('delete_time',null);
    }

    protected function scopeAccount($q,$v)
    {
        return $q -> where('account',$v) -> where('delete_time',null);
    }

    protected function scopeUsername($q,$v)
    {
        return $q -> where('username',$v) -> where('delete_time',null);
    }

    protected function getUsernameAttr($name)
    {
        return stripslashes($name);
    }

    protected function getAccountAttr($name)
    {
        return stripslashes($name);
    }

    protected function getNameAttr($name)
    {
        return stripslashes($name);
    }

    protected function getUrlAttr($name)
    {
        return stripslashes($name);
    }

    protected function getEmailAttr($name)
    {
        return stripslashes($name);
    }


}