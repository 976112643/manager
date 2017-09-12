<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/1
 * Time: 11:49
 */
namespace Api\Controller\Login;

use Api\Controller\Base\BaseController;

/**
 * 登录基础类实现
 * @package Api\Controller\Login
 */
abstract class Base extends BaseController
{
    protected $table;

    /**
     * 实例化普通登录类
     * @param $type
     * @return CustomLogin|ExpertLogin|ManagerLogin|MemberLogin
     */
    public static function getInstance()
    {
        return new MemberLogin();
    }

    /**
     * 实例化第三方登录类
     * @return QqLogin|SinaLogin|WxLogin
     */
    public static function getExtendInstance()
    {
        $posts = I('post.');
        if ( $posts['qq_open_id'] ){
            return new QqLogin();
        }elseif ( $posts['wx_open_id'] ){
            return new WxLogin();
        }elseif ( $posts['sina_open_id'] ){
            return new SinaLogin();
        }else{
            $base = new BaseController();
            $base->set_error( '不允许的第三方登录类型' );
        }
    }

    abstract function login();

    abstract function return_user_info();
}