<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/1/29
 * Time: 22:45
 */

namespace app\index\controller;


class Captcha extends Base
{
    public function index()
    {
        return $this->display("{:captcha_img()}");
    }

    /**
     * 校验验证码
     * @param $code
     * @param bool $return 不直接输出文字
     * @return bool|string
     */
    public function check($code,$return = true)
    {
        if (captcha_check($code))
        {
            return $return?true:'验证码正确';
        }
        else {
            return $return?false:'验证码错误';
        }
    }
}