<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/2/2
 * Time: 21:58
 */

namespace app\index\validate;


use think\Validate;

class LoginLog extends Validate
{
    protected $rule = [
        'uid' => 'require|\d+|token'
    ];

}