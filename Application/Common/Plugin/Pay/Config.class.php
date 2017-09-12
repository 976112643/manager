<?php
namespace Common\Plugin\Pay;
/**
 * 支付配置类
 */
class Config
{
	/**
	 * 支付宝配置
	 * @return [type] [description]
	 */
	public static function ali()
	{
		$pay = C('payment.alipay');
		$config = array(
			'email' 				=>	$pay['email'],
			'key' 					=>	$pay['key'],
			'partner' 				=>	$pay['partner'],
			'ali_public_key_path' 	=>	$pay['ali_public_key_path'],
			'app_private_key_path'  =>  $pay['app_private_key_path'],
			'app_id'				=>  $pay['app_id'],
		);
		return $config;
	}
	/**
	 * 微信配置
	 * @return [type] [description]
	 */
	public static function wx()
	{
		$pay = C('payment.wxpay');
		$config = array(
			'appid' 			=>	$pay['appid'],
			'mch_id' 			=>	$pay['mchid'],
			'partnerkey' 		=>	$pay['key'],
			'appsecret' 		=>	$pay['appsecret'],
			'ssl_key'			=>	$pay['ssl_key'],
			'ssl_cer'			=>	$pay['ssl_cer']
		);
		return $config;
	}
}