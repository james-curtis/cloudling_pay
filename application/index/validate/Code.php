<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/2/2
 * Time: 14:44
 */

namespace app\index\validate;


use think\Validate;

class Code extends Validate
{
    protected $rule = [
        'email|邮箱' => 'require|email',
        'phone|手机号' => ['require','regex' => '/0?(13|14|15|17|18|19)[0-9]{9}/'],
        'code' => 'require',

    ];

    protected $scene = [
        'email' => ['email','code'],
        'phone' => ['phone','code'],
    ];

}