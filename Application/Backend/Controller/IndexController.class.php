<?php
namespace Backend\Controller;

use Common\Controller\CommonController;
use Think\Controller;

/**
 * 后台入口
 */
class IndexController extends CommonController//Controller//CommonController
{

    /**
     * 自动加载
     */
    protected function __autoload()
    {
        if (session('member_id')) {
            header('location:' . U("Backend/Base/Index/index"));
        }
    }

    /**
     * 首页
     */
    public function index()
    {
        $this->display();
    }
}