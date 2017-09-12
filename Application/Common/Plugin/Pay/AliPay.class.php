<?php
namespace Common\Plugin\Pay;

/**
 * 基础框架-支付宝类
 */
class AliPay extends Pay
{
    /** 支付类对象 */
    public $pay_obj;
	public function __construct( $config = array() ) 
	{
		$this->config = $config;
		if (! $this->config['email'] || ! $this->config['key'] || ! $this->config['partner']) {
            throw new \Exception("支付宝配置出错", 1); 
        }
        vendor('alipay.AopClient');
        $this->pay_obj = new \AopClient();
        /** 设置支付公钥 */
        $this->pay_obj->alipayrsaPublicKey = file_get_contents($this->config['ali_public_key_path']);
        /** 设置应用私钥 */
        $this->pay_obj->rsaPrivateKey  = file_get_contents($this->config['app_private_key_path']);
    }
    /** 
     * 获取支付回调的数据
     * @return [type] [description]
     */
	public function getNotifyData()
	{
        $post = $_POST;
        if( $post['out_trade_no'] ){
            $map = array(
                'order_no'  =>  $post['out_trade_no'],
                'status'    =>  '10'
            );
            $order_info = get_info($this->pay_table,$map);
        }
        $info = array();
        $info['post'] = $post;
        $info['order_info'] = $order_info ? $order_info : array();
        return $info;
	}
    /**
     * 支付宝验证
     */
    public function check( $notify )
    {
        /** 验证支付宝支付宝签名 */
        $flag = $this->pay_obj->rsaCheckV1($_POST, NULL, "RSA2");
        if( $flag === false ){
            return false;
        }
        /** 验证支付宝订单状态 */
        if( $notify['post']['trade_status'] != 'TRADE_SUCCESS' ){
            return false;
        }
        /** 验证订单ID */
        if( !$notify['order_info']['id'] ){
            return false;
        }
        /** 验证订单金额 */
        if( $notify['order_info']['money_total'] != $notify['post']['buyer_pay_amount']){
            return false;
        }
        /** 验证商户ID */
        if( $notify['post']['seller_id'] != $this->config['partner'] ){
            return false;
        }
        return true;
    }
    /** 
     * 返回第三方支付信息
     * @param  [type] $flag [description]
     * @return [type]       [description]
     */
	public function returnNotifyData( $flag )
	{
		if( $flag === true ){
            echo "success";
        }else{
            echo "fail";
        }
        exit;
	}
    /**
     * 处理业务逻辑
     */
    public function call( $notify )
    {
        $M = M();
        try{
            $M->startTrans();
            $order_info = $notify['order_info'];
            /**改变订单状态*/
            $data_1 = array(
                'id'            =>  $order_info['id'],
                'pay_no'        =>  $notify['post']['trade_no'],
                'status'        =>  20,
                'pay_type'      =>  10,
                'money_real'    =>  $notify['post']['buyer_pay_amount'],
                'pay_time'      =>  date('Y-m-d H:i:s',time()),
            );
            $res1 = update_data($this->pay_table,[],[],$data_1);/*修改订单信息*/
            if( !is_numeric( $res1 ) ){
                throw new \Exception("修改订单状态失败！", 1);
            }
            /** 修改资金记录 */
            $data_2=array(
                'type'              =>  23, //支付宝APP支付
                'order_no'          =>  $order_info['order_no'],
                'deal_no'           =>  $notify['post']['trade_no'],
                'from_member_id'    =>  $order_info['member_id'],
                'to_member_id'      =>  0,
                'money'             =>  $order_info['money_total'],
                'status'            =>  20,
                'remark'            =>  '支付宝订单支付',
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
            $order_info = $notify['order_info'];
            /**改变订单状态*/
            $data_1 = array(
                'id'            =>  $order_info['id'],
                'pay_no'        =>  $notify['post']['trade_no'],
                'status'        =>  20,
                'pay_type'      =>  10,
                'money_real'    =>  $notify['post']['buyer_pay_amount'],
                'pay_time'      =>  date('Y-m-d H:i:s',time()),
            );
            $res1 = update_data($this->pay_table,[],[],$data_1);/*修改订单信息*/
            if( !is_numeric( $res1 ) ){
                throw new \Exception("修改订单状态失败！", 1);
            }
            /** 修改资金记录 */
            $data_2=array(
                'type'              =>  11, //支付宝充值
                'order_no'          =>  $order_info['order_no'],
                'deal_no'           =>  $notify['post']['trade_no'],
                'from_member_id'    =>  $order_info['member_id'],
                'to_member_id'      =>  0,
                'money'             =>  $order_info['money_total'],
                'status'            =>  20,
                'remark'            =>  '支付宝订单支付',
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
     * 单笔转账到支付宝账户接口
     */
    public function transfer( $order_info )
    {
        /** 设置支付参数 */
        $this->pay_obj->appId = $this->config['app_id'];
        $this->pay_obj->signType = 'RSA2';
        vendor('alipay.request.AlipayFundTransToaccountTransferRequest');
        $request = new \AlipayFundTransToaccountTransferRequest();
        /** 拼接订单信息 */
        $remark = json_decode($order_info['remark'],true);
        $info = array(
            'out_biz_no'        => $order_info['order_no'],
            'payee_type'        => 'ALIPAY_LOGONID',
            'payee_account'     => $remark['account'],
            'amount'            => $order_info['charge'],
            'payer_show_name'   =>'顺手APP提现',
            'payee_real_name'   => '',
            'remark'            =>'顺手APP提现'
        );
        $request->setBizContent( json_encode($info) );

        $result = $this->pay_obj->execute ( $request); 
        write_debug($result,'支付宝提现');
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            return $result;
        } else {
            return false;
        }
    }
}
?>