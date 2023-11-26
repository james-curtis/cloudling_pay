<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/2/1
 * Time: 12:11
 */

define('MOD','api');//合作支付商户

// [ 应用入口文件 ]

// 定义应用目录
define('APP_PATH', __DIR__ . '/./application/');
//绑定访问
$act=isset($_GET['act'])?($_GET['act']):null;
define('BIND_MODULE','index/api/'.$act);
// 加载框架引导文件
require __DIR__ . '/./thinkphp/start.php';