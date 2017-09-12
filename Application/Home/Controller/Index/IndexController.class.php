<?php
namespace Home\Controller\Index;

use Think\Controller;
/**
 * Home前台默认访问类
 * @author Administrator
 *
 */
class IndexController extends Controller
{
    /**
     * Home前台默认控制器
     * @author Administrator
     *
     */
    public function index()
    {
        
    }
    /**
     * 測試地圖
     */
    public function test()
    {
    	$this->display();
    }
}