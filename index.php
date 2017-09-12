<?php
/* version 1.17.0123 */

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// define('DOMAIN','.localhost');
// ini_set('session.cookie_domain',DOMAIN);//跨域访问Session
// 应用入口文件

// 检测PHP环境
if (version_compare(PHP_VERSION, '5.3.0', '<'))
    die('require PHP > 5.3.0 !');


//echo json_encode($_SERVER);
    // 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG', True);

// 定义应用目录
define('APP_PATH', './Application/');

/**
 * 缓存目录设置
 * 此目录必须可写，建议移动到非WEB目录
 */
define('RUNTIME_PATH', './Runtime/');

$session_name = session_name();
if (isset($_POST[$session_name])) {
    session_id($_POST[$session_name]);
}


// 引入ThinkPHP入口文件
require './Core/ThinkPHP.php';


// 亲^_^ 后面不需要任何代码了 就是如此简单