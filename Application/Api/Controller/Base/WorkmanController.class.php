<?php

namespace Api\Controller\Base;

use Workerman\Worker;
use Workerman\WebServer;
use Workerman\Lib\Timer;
/**
 * Workman处理请求
 */
class WorkmanController extends BaseController
{
    /**
     * workman处理各种请求
     */
    public function index()
    {
        // if(!IS_CLI){
        // die("access illegal");
        // }
        require_once APP_PATH . 'workerman/Autoloader.php';
        $http_worker = new Worker("text://0.0.0.0:2345");
        
        // 启动4个进程对外提供服务
        $http_worker->count = 4;
        
        // 接收到浏览器发送的数据时回复hello world给浏览器
        $http_worker->onMessage = function ($connection, $data) {
            // 向浏览器发送hello world
            $data = json_decode($data, true) ? json_decode($data, true) : $data;
            write_debug($data, 'workman测试');
            $connection->send('hello world');
        };
        
        // 运行worker
        Worker::runAll();
    }
}

?>