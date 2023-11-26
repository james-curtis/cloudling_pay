<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/2/3
 * Time: 14:40
 */

namespace app\index\validate;


use think\Validate;

class Pay extends Validate
{
    protected $rule = [
        'pid' => 'require|number',
        'type' => ['require','alphaNum','regex' => '^(alipay|qqpay|wxpay|tenpay|1|2|3|4){1}$'],
        'out_trade_no' => 'require|number',
        'notify_url' => 'require|url',
        'return_url' => 'require|url',
        'name' => 'require',
        'money' => 'require|float',
        'sitename' => 'chsDash',
        'sign' => 'require|alphaNum',
        'sign_type' => ['require','regex' => '^(DSA|RSA|MD5){1}$'],
    ];

}