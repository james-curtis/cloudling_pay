<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/2/2
 * Time: 13:37
 */

namespace app\index\validate;


use think\Validate;

class User extends Validate
{
    protected $rule = [
        'username|用户名' => 'require|max:16',
        'email|邮箱' => 'require|email',
        'phone|手机号' => ['require','regex' => '0?(13|14|15|17|18|19)[0-9]{9}'],
        'pwd|密码' => 'require|max:16',
        'code|验证码' => 'require',
        'balance|结算方式' => 'require|\d{1}|token',
        'account|结算账号' => 'require|max:32',
        'name|真实姓名' => 'require|max:10',
        'qq|QQ' => 'require|[1-9]([0-9]{5,11})',
        'url|网址' => ['require','regex' => '^((http:\/\/)|(https:\/\/)){1}([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}(\/){1}'],

    ];

    protected $message = [
        'url.regex' => '网址需要带http://或者https://和后缀/',
    ];

    protected $scene = [
        'regWithEmail' => ['username','email','pwd','code'],
        'regWithPhone' => ['username','phone','pwd','code'],
        'login' => ['username','pwd'],
        'resetBalance' => ['balance','account','name'],
        'resetContact' => ['qq','url'],
    ];


}