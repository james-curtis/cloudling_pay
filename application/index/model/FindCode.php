<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/2/2
 * Time: 17:26
 */

namespace app\index\model;


use think\Model;

class FindCode extends Model
{
    protected $insert = [
        'ip',
    ];

    protected $updateTime = false;

    protected $createTime = 'time';

    protected function setIpAttr()
    {
        return request() -> ip();
    }

    protected function setVerifytypeAttr($v)
    {
        return $v == 'phone'?1:0;
    }

    protected function getVerifytypeAttr($v)
    {
        return $v == 1?'phone':'email';
    }

}