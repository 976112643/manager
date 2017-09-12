<?php
namespace Common\Plugin\Pay;

use Wechat\WechatPay;
/**
 * 基础框架-微信类
 */
class WxPay extends Pay
{
	public function __construct( $config = array() ) 
	{
		$this->config = $config;
		if (! $this->config['appid'] || ! $this->config['mch_id'] || ! $this->config['partnerkey']) {
            throw new \Exception("微信支付配置出错", 1); 
        }
        vendor('Wechat.Loader');
        $this->pay_obj = new WechatPay( $this->config );
    }
    /** 
     * 获取支付回调的数据
     * @return [type] [description]
     */
	public function getNotifyData()
	{
        return $this->pay_obj->getNotify();
	}
    /**
     * 微信验证
     */
    public function check( $notify )
    {
        if($notify['return_code'] == 'SUCCESS' && $notify['result_code'] == 'SUCCESS'){
            return true;
        }else{
            return false;
        }
    }
    /** 
     * 返回第三方支付信息
     * @param  [type] $flag [description]
     * @return [type]       [description]
     */
	public function returnNotifyData( $flag = false )
	{
        return $this->pay_obj->notifyReturn( $flag );
	}
    /**
     * 支付回调的处理
     * 根据不同的项目进行处理
     */
    public function call( $notify )
    {
        $M = M();
        try{
            $M->startTrans();
            $map['order_no'] = $notify['out_trade_no'];
            $map['status'] = 10;
            $order_info = get_info($this->pay_table,$map);

            $wx_total = $notify['total_fee'] / 100;

            if( !$order_info['id'] || ($order_info['money_total'] != $wx_total) ){
                throw new \Exception("订单不存在或者订单价格不正确", 1);    
            }
            /**改变订单状态*/
            $data_1 = array(
                'id'            =>  $order_info['id'],
                'pay_no'        =>  $notify['transaction_id'],
                'status'        =>  20,
                'pay_type'      =>  20,
                'money_real'    =>  $notify['total_fee'] / 100,
                'pay_time'      =>  date('Y-m-d H:i:s',time()),
            );
            $res1 = update_data($this->pay_table,[],[],$data_1);/*修改订单信息*/
            if( !is_numeric( $res1 ) ){
                throw new \Exception("修改订单状态失败！", 1);
            }
            /** 修改资金记录 */
            $data_2=array(
                'type'              =>  22, //微信支付
                'order_no'          =>  $order_info['order_no'],
                'deal_no'           =>  build_order_no(),
                'from_member_id'    =>  $order_info['member_id'],
                'to_member_id'      =>  0,
                'money'             =>  $order_info['money_total'],
                'status'            =>  20,
                'remark'            =>  '微信订单支付',
            );
            $res2 = update_data('capital_record',[],[],$data_2);/*修改资金记录*/
            if( !is_numeric( $res2 ) ){
                throw new \Exception("记录资金异常", 1);
            }
            /** 增加订单操作记录 */
            order_log($order_info['id'],$order_info['member_id'],'',"用户支付订单",20);
                
            /*更新销售记录*/
            saller_order_log(0,$order_info['id'],0,$order_info['money_total'],$order_info['order_no']);

            /*下单发送系统站内信*/
            // $content = '订单支付成功了,订单号是：'.$order_info['order_no'].',客户ID：'.$order_info['member_id'].',地址是：'.$order_info['address'].',手机号是:'.$order_info['receipt_mobile'].',截止时间是:'.$order_info['door_time'];
            // $data = array('content'=>$content,'title'=>'订单支付成功了,订单号是:'.$order_info['order_no'],'send_name'=>'系统管理员');
            // send_message_one(5,0,0,$data);

        } catch (\Exception $e) {
            $M->rollback();
            return $e->getMessage();
        }
        $M->commit();
        return $res1;
    }
    /**
     * 充值回调
     */
    public function recharge_call( $notify )
    {
        $M = M();
        try{
            $M->startTrans();
            $map['order_no'] = $notify['out_trade_no'];
            $map['status'] = 10;
            $order_info = get_info($this->pay_table,$map);

            $wx_total = $notify['total_fee'] / 100;

            if( !$order_info['id'] || ($order_info['money_total'] != $wx_total) ){
                throw new \Exception("订单不存在或者订单价格不正确", 1);    
            }
            /**改变订单状态*/
            $data_1 = array(
                'id'            =>  $order_info['id'],
                'pay_no'        =>  $notify['transaction_id'],
                'status'        =>  20,
                'pay_type'      =>  20,
                'money_real'    =>  $notify['total_fee'] / 100,
                'pay_time'      =>  date('Y-m-d H:i:s',time()),
            );
            $res1 = update_data($this->pay_table,[],[],$data_1);/*修改订单信息*/
            if( !is_numeric( $res1 ) ){
                throw new \Exception("修改订单状态失败！", 1);
            }
            /** 修改资金记录 */
            $data_2=array(
                'type'              =>  10, //微信充值
                'order_no'          =>  $order_info['order_no'],
                'deal_no'           =>  $notify['transaction_id'],
                'from_member_id'    =>  $order_info['member_id'],
                'to_member_id'      =>  0,
                'money'             =>  $order_info['money_total'],
                'status'            =>  20,
                'remark'            =>  '微信充值',
            );
            $res2 = update_data('capital_record',[],[],$data_2);/*修改资金记录*/
            if( !is_numeric( $res2 ) ){
                throw new \Exception("记录资金异常", 1);
            }
            /** 增加用户余额 */
            M('member')->where(array('id'=>$order_info['member_id']))->setInc('balance',$order_info['money_total']);
            /*更新销售记录*/
            saller_order_log(2,$order_info['id'],0,$order_info['money_total'],$order_info['order_no']);

        } catch (\Exception $e) {
            $M->rollback();
            return $e->getMessage();
        }
        $M->commit();
        return $res1;
    }
    /**
     * 生成APP端支付的预支付ID
     */
    public function create_pay_id( $config )
    {
        return $this->pay_obj->getPrepayId(
                $config['openid'],
                $config['body'],
                $config['order_no'],
                $config['money_total'],
                $config['notify_url'],
                $config['trade_type']
        );
    }
    /**
     * 企业付款到个人
     */
    public function transfer($order_info)
    {
        if( !$order_info ) return false;
        $remark = json_decode($order_info['remark'],true);
        $openid = $remark['account'];
        $amount = $order_info['charge'] * 100;
        $billno = $order_info['order_no'];
        $body = '顺手APP提现';
        $res = $this->pay_obj->transfers($openid,$amount,$billno,$body);
        write_debug($res,'顺手微信提现');
        if( $res === false ){
            return $res->errCode . $res->errMsg;
        }
        return $res;
    }


    /**
     * 返回微信支付类
     */
    public function get_pay_obj()
    {
        return $this->pay_obj;
    }
}
?>