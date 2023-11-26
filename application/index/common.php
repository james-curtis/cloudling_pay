<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/2/1
 * Time: 20:02
 */

function codepay_create_link($params, $codepay_key, $host = "")
{
    ksort($params);
    reset($params);
    $sign = '';
    $urls = '';
    foreach ($params as $key => $val) {
        if ($val == '') {
            continue;
        }
        if ($key != 'sign') {
            if ($sign != '') {
                $sign .= "&";
                $urls .= "&";
            }
            $sign .= "{$key}={$val}";
            $urls .= "{$key}=" . urlencode($val);
        }
    }
    $key = md5($sign . $codepay_key);
    $query = $urls . '&sign=' . $key;
    $apiHost = $host ? $host : "http://api2.fateqq.com:52888/creat_order/?";
    $url = $apiHost . $query;
    return array("url" => $url, "query" => $query, "sign" => $sign, "param" => $urls);
}