<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/1
 * Time: 14:48
 */
namespace Api\Controller\Login;
/**
 * SINA 登录类
 * @package Api\Controller\Login
 */
class SinaLogin extends MemberLogin
{
    /**
     * 登录
     */
    public function login()
    {
        $check = new CheckLogin();
        $check->extend( new self() , $this->table );
    }
    /**
     * 获取搜索条件
     */
    public function get_map()
    {
        $map = array(
            'sina_open_id' => I('sina_open_id')
        );
        return $map;
    }
}