<?php 
namespace Api\Controller\Pay;

use Api\Controller\Base\BaseController;
use Common\Plugin\Pay\Pay;

/**
 * 【通用】支付回调控制器
 * @author Administrator
 *
 */
class CallbackController extends BaseController
{
    private $pay;
    private $demand;
    
    public function test()
    {
        $pay = Pay::getInstance('ali');
    }
    
    /**
     * 支付宝回调
     */
    public function ali_callback()
    {
        $pay = Pay::getInstance('ali');

        /** 获取支付宝回调的数据 */
        $notifyInfo = $pay->getNotifyData();
        write_debug($notifyInfo,'用户端支付宝支付回调信息');
        //如果支付宝推送数据验签成功
        $flag = $pay->check( $notifyInfo );

        if( $flag === true ){
            /** 验证通过后，处理项目业务逻辑 */
            $res = $pay->call( $notifyInfo );

            if( is_numeric($res) ){
                $pay->returnNotifyData( true );
            }else{
                write_debug($res,'支付宝支付回调失败');
                $pay->returnNotifyData( false );
            }
        }else{
            /** 通知支付宝 */
            $pay->returnNotifyData( false );
        }
    }
    /**
     * 订单余额支付
     * @author 鲍海
     * @time 2017-03-24
     */
    public function default_callback()
    {
        $pay = Pay::getInstance('ye');

        $notifyInfo = $pay->getNotifyData();

        write_debug($notifyInfo,'用户端余额支付');

        $flag = $pay->check( $notifyInfo );

        if( $flag === true ){
            /** 验证通过后，处理项目业务逻辑 */
            $res = $pay->call( $notifyInfo );

            if( is_numeric($res) ){
                $pay->returnNotifyData( true );
            }else{
                write_debug($res,'余额支付失败');
                $pay->returnNotifyData( false );
            }
        }else{
            /** 通知用户端 */
            $pay->returnNotifyData( false );
        } 
    }
    
    /**
     * 微信支付回调
     * @author 鲍海
     * @time 2017-03-24
     */
    public function weixin_callback()
    {
        $pay = Pay::getInstance('wx');

        /** 获取微信回调的数据 */
        $notifyInfo = $pay->getNotifyData();

        write_debug($notifyInfo,'用户端微信支付回调信息');
        //如果微信推送数据验签成功
        $flag = $pay->check( $notifyInfo );

        if( $flag === true ){
            /** 验证通过后，处理项目业务逻辑 */
            $res = $pay->call( $notifyInfo );

            if( is_numeric($res) ){
                $pay->returnNotifyData( true );
            }else{
                write_debug($res,'微信支付回调失败');
                $pay->returnNotifyData( false );
            }
        }else{
            /** 通知微信 */
            $pay->returnNotifyData( false );
        }
    }
    /**
     * 支付宝充值回调
     */
    public function ali_recharge_callback()
    {
        $pay = Pay::getInstance('ali');

        $pay->set_pay_table('recharge_order');
        /** 获取支付宝回调的数据 */
        $notifyInfo = $pay->getNotifyData();
        
        write_debug($notifyInfo,'用户端支付宝充值回调信息');
        //如果支付宝推送数据验签成功
        $flag = $pay->check( $notifyInfo );

        if( $flag === true ){
            /** 验证通过后，处理项目业务逻辑 */
            $res = $pay->recharge_call( $notifyInfo );

            if( is_numeric($res) ){
                $pay->returnNotifyData( true );
            }else{
                write_debug($res,'支付宝充值回调失败');
                $pay->returnNotifyData( false );
            }
        }else{
            /** 通知微信 */
            $pay->returnNotifyData( false );
        }
    }
    /**
     * 微信充值回调
     */
    public function weixin_recharge_callback()
    {
        $pay = Pay::getInstance('wx');

        $pay->set_pay_table('recharge_order');
        /** 获取支付宝回调的数据 */
        $notifyInfo = $pay->getNotifyData();

        write_debug($notifyInfo,'用户端微信充值回调信息');
        //如果微信推送数据验签成功
        $flag = $pay->check( $notifyInfo );

        if( $flag === true ){
            /** 验证通过后，处理项目业务逻辑 */
            $res = $pay->recharge_call( $notifyInfo );

            if( is_numeric($res) ){
                $pay->returnNotifyData( true );
            }else{
                write_debug($res,'微信充值回调失败');
                $pay->returnNotifyData( false );
            }
        }else{
            /** 通知微信 */
            $pay->returnNotifyData( false );
        }
    }
}
?>