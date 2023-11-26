<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

//use mailer\Stmp;

function get_config()
{
    $db = db('config');
    $result = $db->select();
    $set = null;
    foreach ($result as $value) {
        $set[$value['k']] = $value['v'];
    }

    return $set;
}

function makeBreadcrumbs($crumbs = array())
{
    /*
    <li><a href="#">Dashboard</a></li>
    <li><a href="#">Forms</a></li>
    <li class="active">Basic</li>
    */
    if (empty($crumbs)) return null;
    //最后一个单独处理
//    $keys = key($crumbs);
//    $last_crumb = $keys[count($crumbs)-1];
//    array_pop($crumbs);

    $html = '';
    $count = 0;
    foreach ($crumbs as $crumb => $url) {
        if ($count == count($crumbs) - 1) {
            $html .= '<li class="active">' . $crumb . '</li>';
            break;
        }

        $html .= '<li><a href="';
        $html .= empty($url) ? '#' : $url;
        $html .= '">';
        $html .= $crumb;
        $html .= '</a></li>';

        $count++;
    }

    //处理active
    //$html .= '<li class="active">'.$last_crumb.'</li>';
    return $html;


}


function random($length, $numeric = 0)
{
    $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    $hash = '';
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed{mt_rand(0, $max)};
    }
    return $hash;
}

function get_city_by_ip($ip)
{
    $api = 'http://ip.taobao.com/service/getIpInfo.php?ip=';
    $content = file_get_contents($api . $ip);
    $obj = json_decode($content, true);
//    dump($content);
    if ($obj['code'] == 0) {
        $obj = $obj['data'];
        return $obj['country'] . $obj['region'] . $obj['city'] . $obj['isp'];
    }
}


/**
 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
 * @param $para 需要拼接的数组
 * return 拼接完成以后的字符串
 */
function createLinkstring($para)
{
    $arg = "";
    while (list ($key, $val) = each($para)) {
        $arg .= $key . "=" . $val . "&";
    }
    //去掉最后一个&字符
    $arg = substr($arg, 0, count($arg) - 2);

    //如果存在转义字符，那么去掉转义
    if (get_magic_quotes_gpc()) {
        $arg = stripslashes($arg);
    }

    return $arg;
}

/**
 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
 * @param $para 需要拼接的数组
 * return 拼接完成以后的字符串
 */
function createLinkstringUrlencode($para)
{
    $arg = "";
    while (list ($key, $val) = each($para)) {
        $arg .= $key . "=" . urlencode($val) . "&";
    }
    //去掉最后一个&字符
    $arg = substr($arg, 0, count($arg) - 2);

    //如果存在转义字符，那么去掉转义
    if (get_magic_quotes_gpc()) {
        $arg = stripslashes($arg);
    }

    return $arg;
}

/**
 * 除去数组中的空值和签名参数
 * @param $para 签名参数组
 * return 去掉空值与签名参数后的新签名参数组
 */
function paraFilter($para)
{
    $para_filter = array();
    while (list ($key, $val) = each($para)) {
        if ($key == "sign" || $key == "sign_type" || $val == "") continue;
        else    $para_filter[$key] = $para[$key];
    }
    return $para_filter;
}

/**
 * 对数组排序
 * @param $para 排序前的数组
 * return 排序后的数组
 */
function argSort($para)
{
    ksort($para);
    reset($para);
    return $para;
}

/**
 * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
 * 注意：服务器需要开通fopen配置
 * @param $word 要写入日志里的文本内容 默认值：空值
 */
function logResult($word = '')
{
    $fp = fopen("log.txt", "a");
    flock($fp, LOCK_EX);
    fwrite($fp, "执行日期：" . strftime("%Y%m%d%H%M%S", time()) . "\n" . $word . "\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}

/**
 * 远程获取数据，POST模式
 * 注意：
 * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
 * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
 * @param $url 指定URL完整路径地址
 * @param $cacert_url 指定当前工作目录绝对路径
 * @param $para 请求的数据
 * @param $input_charset 编码格式。默认值：空值
 * return 远程输出的数据
 */
function getHttpResponsePOST($url, $cacert_url, $para, $input_charset = '')
{

    if (trim($input_charset) != '') {
        $url = $url . "_input_charset=" . $input_charset;
    }
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
    curl_setopt($curl, CURLOPT_CAINFO, $cacert_url);//证书地址
    curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
    curl_setopt($curl, CURLOPT_POST, true); // post传输数据
    curl_setopt($curl, CURLOPT_POSTFIELDS, $para);// post传输数据
    $responseText = curl_exec($curl);
    //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
    curl_close($curl);

    return $responseText;
}

/**
 * 远程获取数据，GET模式
 * 注意：
 * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
 * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
 * @param $url 指定URL完整路径地址
 * @param $cacert_url 指定当前工作目录绝对路径
 * return 远程输出的数据
 */
function getHttpResponseGET($url, $cacert_url)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
    curl_setopt($curl, CURLOPT_CAINFO, $cacert_url);//证书地址
    $responseText = curl_exec($curl);
    //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
    curl_close($curl);

    return $responseText;
}

/**
 * 实现多种字符编码方式
 * @param $input 需要编码的字符串
 * @param $_output_charset 输出的编码格式
 * @param $_input_charset 输入的编码格式
 * return 编码后的字符串
 */
function charsetEncode($input, $_output_charset, $_input_charset)
{
    $output = "";
    if (!isset($_output_charset)) $_output_charset = $_input_charset;
    if ($_input_charset == $_output_charset || $input == null) {
        $output = $input;
    } elseif (function_exists("mb_convert_encoding")) {
        $output = mb_convert_encoding($input, $_output_charset, $_input_charset);
    } elseif (function_exists("iconv")) {
        $output = iconv($_input_charset, $_output_charset, $input);
    } else die("sorry, you have no libs support for charset change.");
    return $output;
}

/**
 * 实现多种字符解码方式
 * @param $input 需要解码的字符串
 * @param $_output_charset 输出的解码格式
 * @param $_input_charset 输入的解码格式
 * return 解码后的字符串
 */
function charsetDecode($input, $_input_charset, $_output_charset)
{
    $output = "";
    if (!isset($_input_charset)) $_input_charset = $_input_charset;
    if ($_input_charset == $_output_charset || $input == null) {
        $output = $input;
    } elseif (function_exists("mb_convert_encoding")) {
        $output = mb_convert_encoding($input, $_output_charset, $_input_charset);
    } elseif (function_exists("iconv")) {
        $output = iconv($_input_charset, $_output_charset, $input);
    } else die("sorry, you have no libs support for charset changes.");
    return $output;
}

/**
 * 签名字符串
 * @param $prestr 需要签名的字符串
 * @param $key 私钥
 * return 签名结果
 */
function md5Sign($prestr, $key)
{
    $prestr = $prestr . $key;
    return md5($prestr);
}

/**
 * 验证签名
 * @param $prestr 需要签名的字符串
 * @param $sign 签名结果
 * @param $key 私钥
 * return 签名结果
 */
function md5Verify($prestr, $sign, $key)
{
    $prestr = $prestr . $key;
    $mysgin = md5($prestr);

    if ($mysgin == $sign) {
        return true;
    } else {
        return false;
    }
}

function isHTTPS()
{
    if (defined('HTTPS') && HTTPS) {
        return true;
    }
    if (!isset($_SERVER)) {
        return FALSE;
    }
    if (!isset($_SERVER['HTTPS'])) {
        return FALSE;
    }
    if ($_SERVER['HTTPS'] === 1) {
        return TRUE;
    } elseif ($_SERVER['HTTPS'] === 'on') {
        return TRUE;
    } elseif ($_SERVER['SERVER_PORT'] == 443) {
        return TRUE;
    }
    return FALSE;
}

function str_exists($string, $find)
{
    return !(strpos($string, $find) === FALSE);
}

function get_domain($url)
{
    $arr = parse_url($url);
    return $arr['host'];
}

function daddslashes($string, $force = 0, $strip = FALSE)
{
    !defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
    if (!MAGIC_QUOTES_GPC || $force) {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = daddslashes($val, $force, $strip);
            }
        } else {
            $string = addslashes($strip ? stripslashes($string) : $string);
        }
    }
    return $string;
}

function re_charset($data = [], $out_charset = 'gbk', $in_charset = 'utf-8')
{
    $da = [];
    foreach ($data as $k => $v) {
        if (is_array($v))
        {
            $da[$k] = re_charset($v,$out_charset,$in_charset);
        }
        else
        {
            $da[$k] = iconv($in_charset, $out_charset, $v);
        }
    }
    return $da;
}

function strexists($string, $find) {
    return !(strpos($string, $find) === FALSE);
}

function curl_get($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; U; Android 4.4.1; zh-cn; R815T Build/JOP40D) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/4.5 Mobile Safari/533.1');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $content = curl_exec($ch);
    curl_close($ch);
    return ($content);
}

/**
 * ajax下确定表单令牌
 * @param $token
 * @return bool
 */
function ajax_check_token($token)
{
    if (\think\Session::has('__token__') && \think\Session::get('__token__') == $token)
    {
        return true;
    }
    else
    {
        return false;
    }
}

function create_callback_url($order)
{
    $user = \app\index\model\User::get(['pid' => $order->pid]);
    $data = $order;
    $array = array(
        'pid' => $user->pid,
        'trade_no' => $data['trade_no'],
        'out_trade_no' => $data['out_trade_no'],
        'type' => $data['type'],
        'name' => $data['name'],
        'money' => $data['money'],
        'trade_status' => 'TRADE_SUCCESS'
    );
    $arg = argSort(paraFilter($array));
    $prestr = createLinkstring($arg);
    $urlstr = createLinkstringUrlencode($arg);
    $sign = md5Sign($prestr, $user->key);
    if (strpos($data['notify_url'], '?'))
        $url['notify'] = $data['notify_url'] . '&' . $urlstr . '&sign=' . $sign . '&sign_type=MD5';
    else
        $url['notify'] = $data['notify_url'] . '?' . $urlstr . '&sign=' . $sign . '&sign_type=MD5';
    if (strpos($data['return_url'], '?'))
        $url['return'] = $data['return_url'] . '&' . $urlstr . '&sign=' . $sign . '&sign_type=MD5';
    else
        $url['return'] = $data['return_url'] . '?' . $urlstr . '&sign=' . $sign . '&sign_type=MD5';
    return $url;
}





