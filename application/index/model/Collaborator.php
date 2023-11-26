<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/2/5
 * Time: 23:51
 */

namespace app\index\model;


use think\Model;

class Collaborator extends Model
{
    protected $createTime = 'regtime';

    protected $updateTime = false;

    protected $insert = [
        'active' => 1,
    ];

    protected function setPwdAttr($pwd)
    {
        return md5($pwd);
    }
}