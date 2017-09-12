<?php
namespace Common\Plugin\Pay;

/**
 * 尚软科技-支付抽象类
 */
abstract class Pay
{
	/** 支付配置 */
	protected $config;

	/** 第三方支付对象 */
	protected $pay_obj;
    /**
     * 查询的订单表
     */
    protected $pay_table = 'order';


    /**
     * 实例函数，单例入口
     * 共有，静态函数
     * @param array $options 实例化配置
     * @return resource
     */
    public static function getInstance($type = '')
    {
        switch (strtolower($type)) {
        	case 'ali': //支付宝支付
        		$pay = new AliPay(Config::ali());
        		break;
        	case 'wx':  //微信支付
        		$pay = new WxPay(Config::wx());
        		break;
        	case 'ye':  //余额支付
        		$pay = new YePay();
        		break;
        	default:
        		$pay = new YePay();
        		break;
        }
        return $pay;
    }
    /**
     * 设置查询的订单表
     * @param string $table [description]
     */
    public function set_pay_table( $table = 'order')
    {
        $this->pay_table = $table;
    }
    /** 验证 */
	abstract function check( $notify);
	/** 获取支付回调的数据 */
	abstract function getNotifyData();
	/** 返回第三方支付信息 */
	abstract function returnNotifyData( $flag );
    /** 处理项目业务逻辑 */
    abstract function call( $notify );
}
?>