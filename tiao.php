<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/2/7
 * Time: 18:12
 */

//echo 1;
//var_dump($_SERVER);
function isSsl()
{
    $server = $_SERVER;
    if (isset($server['HTTPS']) && ('1' == $server['HTTPS'] || 'on' == strtolower($server['HTTPS']))) {
        return true;
    } elseif (isset($server['REQUEST_SCHEME']) && 'https' == $server['REQUEST_SCHEME']) {
        return true;
    } elseif (isset($server['SERVER_PORT']) && ('443' == $server['SERVER_PORT'])) {
        return true;
    } elseif (isset($server['HTTP_X_FORWARDED_PROTO']) && 'https' == $server['HTTP_X_FORWARDED_PROTO']) {
        return true;
    }
    return false;
}

$direct_domain = 'yun-ling.cn';
$prefix = '';
if (preg_match('/(.+)\..+\..+/',$_SERVER['HTTP_HOST'],$matches) !== false)
{
//    var_dump($matches);
    $prefix = $matches[1].'.';
}


$url = (isSsl()?'https':'http').'://'.$prefix.$direct_domain.($_SERVER['SERVER_PORT']=='80'?'':':'.$_SERVER['SERVER_PORT']).$_SERVER['REQUEST_URI'];
//echo $url;
header('Location: '.$url);