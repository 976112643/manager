<?php
namespace Api\Controller\Login;

use Api\Controller\Base\BaseController;
use Api\Controller\Login\Base;

/** 
 * 登录类
 */
class IndexController extends BaseController
{
	/**
	 * 登录首页
	 * @return [type] [description]
	 */
	public function login()
	{
        $login = Base::getInstance( );
        $login->login();
	}
	/**
	 * 发送登录验证码 
	 */
	public function login_code()
	{
		if( !IS_POST ) $this->set_error('请注意请求方式');
        $mobile = I('post.mobile');
        if( !$mobile ) $this->set_error('请输入手机号');
        if( !preg_match(MOBILE,$mobile) ) $this->set_error('手机号格式不正确');

        $content = $this->send_mobile_code($mobile,1);

        $this->set_success($content);
	}
	/**
	 * 第三方登录
	 * 本APP使用第三方登录的目的:让那些不想使用手机号注册的用户使用
	 * 1、获取用户基础信息
	 * 2、登录或者注册
	 * 3、知情同意书
	 * 4、问卷调查
	 * @return [type] [description]
	 */
	public function extract_login()
	{
		$login = Base::getExtendInstance();
		$login->login();
	}
}

?>