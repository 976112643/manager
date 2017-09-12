<?php
namespace Common\Plugin\Pay;

/**
 * 基础框架-余额类
 */
class YePay extends Pay
{
    /** 错误信息 */
    protected $errorMsg;
	public function __construct(  ) 
	{

    }
    /** 
     * 获取支付回调的数据
     * @return [type] [description]
     */
	public function getNotifyData()
	{
        $post = I('post.');
        /** 查询订单 */
        if( $post['order_id'] ){
            $map = array(
                'id'        =>  $post['order_id'],
                'status'    =>  10
            );
            $info['order_info'] = get_info($this->pay_table,$map);
        }else{
            $info['order_info'] = array();
        }
        $info['pay_password'] = $post['pay_password'];
        return $info;
	}
    /**
     * 余额验证
     */
    public function check( $notify )
    {
        if( !$notify['order_info']['id'] ){
            $this->errorMsg = '不存在的订单';
            return false;
        }
        if( !$notify['pay_password'] ){
            $this->errorMsg = '你得传支付密码给我啊!';
            return false;
        }
        $map = array(
            'id' =>$notify['order_info']['member_id']
        );
        $member = get_info('member',$map,'deal_password,deal_salt,balance');
        /**
         * 查询支付密码是否正确
         */
        $pay_password = md5( md5( $notify['pay_password'] ) . $member['deal_salt'] );
        if( $pay_password != $member['deal_password'] ){
            $this->errorMsg = '支付密码错误';
            return false;
        }
        /**
         * 查询余额是否可以进行订单支付
         */
        if( $member['balance'] < $notify['order_info']['money_total'] ){
            $this->errorMsg = '余额不足';
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
            $data = array('status'=>'1','msg'=>'支付成功','info'=>array());
        }else{
            $data = array('status'=>'0','msg'=>$this->errorMsg,'info'=>array());
        }
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data, 0));
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

            if( !$order_info['id'] ){
                throw new \Exception("订单不存在", 1);    
            }
            /**改变订单状态*/
            $data_1 = array(
                'id'            =>  $order_info['id'],
                'pay_no'        =>  build_order_no(),
                'status'        =>  20,
                'pay_type'      =>  30,
                'money_real'    =>  $order_info['money_total'],
                'pay_time'      =>  date('Y-m-d H:i:s',time()),
            );
            $res1 = update_data($this->pay_table,[],[],$data_1);/*修改订单信息*/
            if( !is_numeric( $res1 ) ){
                throw new \Exception("修改订单状态失败！", 1);
            }
            /** 修改资金记录 */
            $data_2=array(
                'type'              =>  21, //余额支付
                'order_no'          =>  $order_info['order_no'],
                'deal_no'           =>  build_order_no(),
                'from_member_id'    =>  $order_info['member_id'],
                'to_member_id'      =>  0,
                'money'             =>  $order_info['money_total'],
                'charge'             =>  $order_info['money_total'],
                'money_fee'             =>  0,
                'status'            =>  20,
                'way'               =>  1,
                'remark'            =>  '余额订单支付',
            );
            $res2 = update_data('capital_record',[],[],$data_2);/*修改资金记录*/
            if( !is_numeric( $res2 ) ){
                throw new \Exception("记录资金异常", 1);
            }
            /** 减少用户余额 */
            M('member')->where(array('id'=>$order_info['member_id']))->setDec('balance',$order_info['money_total']);
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
}
?>