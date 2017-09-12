<?php
namespace Test\Controller\Index;

use Think\Controller;
trait help {
    public function Exception($msg,$code = '1')
    {
        throw new \Exception($msg,$code);   
    }

};
/**
 * Home前台默认访问类
 * @author Administrator
 *
 */
class IndexController extends Controller
{
    use help;
    /**
     * Home前台默认控制器
     * @author Administrator
     *
     */
    public function index()
    {

    }
}