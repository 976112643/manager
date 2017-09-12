<?php  
namespace Common\Controller\Order;
use Api\Controller\Base\BaseController;
use Api\Controller\V1\DemandController;
/**
 * 加油卡支付主类
 * @author   鲍海
 * @time     2016.12.20
 */
class PayController extends BaseController{
    protected $demand;
    /**
     * 自动加载
     * @return [type] [description]
     */
    public function __autoload(){
        parent::__autoload();
        $this->demand = new DemandController(); /*需求操作类*/
    }

    /**
     * 
     * 充值订单更新
     * 1、改变订单状态
     * 2、更新钱包
     * @param  [type]  $order_info [订单信息]
     * @param  [type]  $deal_no    [第三方支付ID]
     * @param  [type]  $pay_time   [支付时间]
     * @param  integer $type       [支付类型]
     * @return [type]              [事务结果]
     */
    public function funds_order($order_info,$deal_no,$pay_time,$type=1){
        try {
            $M=M();
            $M->startTrans();
            $member_id = $order_info['member_id'];
            $data=array(
                'order_no' =>$order_info['order_no'],
                'type' => $type,
                'from_member_id'=> 0,
                'to_member_id' => $order_info['member_id'],
                'money' => $order_info['money_real'],
                'status' => 10,
                'is_hid'=>1,
            );
            $this->money_recod($data);/*隐藏充值*/
            $data=array(
                'order_no' =>$order_info['order_no'],
                'type' => 0,
                'from_member_id'=> $order_info['member_id'],
                'to_member_id' => 1,
                'money' => $order_info['money_real'],
                'status' => 10,
                'is_hid'=>0,
            );
            $this->money_recod($data);/*资金记录*/
            $data=array(
                'deal_no' =>$deal_no,
                'pay_time'=>$pay_time,
                'type' => $type,
                'status' =>20,
            );
            \Think\Log::write(serialize($data));
            $res=update_data('fuel_card_order','',['id'=>$order_info['id']],$data);
            if(!is_numeric($res)){
                throw new \Exception("订单信息更新异常", 1);
            }
            /*更新用户加油卡可用状态*/
            $this->update_member_fuel_card($order_info['id'],$member_id);/*激活用户加油卡*/
            $this->order_log($order_info['id'],$order_info['member_id'],"用户支付订单",20);/*订单日志*/
            
            /*发送站内信*/
            send_tips($member_id,'购买加油卡支付宝支付成功','订单号：'.$order_info['order_no'].'金额：'.$order_info['money_real']);

        } catch (\Exception $e) {
            $M->rollback();
            return $e->getMessage();
        }
        $M->commit();
        return true;
    }
    
    /**
     * 获取人员信息
     * @param  $[member_id] [人员ID]
     * @return [type] [description]
     */
    public function check_member($member_id){
        $info = get_info('member',array('is_del'=>0,'id'=>$member_id));
        if(!$info){
            throw new \Exception("无此人员信息", 1);
        }
        return $info;
    }

    /**
     * 验证权限
     * 
     * @return [type] [description]
     */
    protected function _is_auth()
    {
        $keys = I('user_key','','trim');
        $data = $this->get_decrypt_key($keys);
        if($data){
            $data = explode('_', $data);
            $this->_id = $data['1'];
        }else{
            $this->set_error('用户密匙错误，请检查参数!');
        }
    }

    /**
     * 余额支付
     * @return [type] [description]
     */
    public function balance_pay(){
        $this->_is_auth();
        $order_id = intval(I('post.order_id'));
        $member_id = intval($this->_id);
        $map['id'] = $order_id;
        $map['status'] = 10;
        $map['member_id'] = $member_id;     
        try {
            $M=M();
            $M->startTrans();
            $order_info = get_info('order',$map);
            if(!$order_info['id']) {
                throw new \Exception("无此订单！", 1);
            }
            $member_info = $this->check_member($member_id);
            $this->check_password($member_info,$order_info);
            $data=array(
                'order_no' =>$order_info['order_no'],
                'type' => 21,
                'from_member_id'=> $order_info['member_id'],
                'to_member_id' =>0,
                'money' => $order_info['money_total'],
                'remark'=> '订单支付',
                'status' => 20,
            );
            $money = $member_info['balance']-$order_info['money_total'];
            $this->update_balance($money,$member_id);/*用户减去余额*/
            $this->money_recod($data);/*资金记录*/

            $data=array(
                'pay_time'=>date('Y-m-d H:i:s'),
                'status' =>20,
                'pay_type'=>30,
                'money_real'=>$order_info['money_total'],
                'pay_time'=>date('Y-m-d H:i:s',time()),
            );
            $res=update_data('order','',['id'=>$order_info['id']],$data);
            if(!is_numeric($res)){
                throw new \Exception("订单信息更新异常", 1);
            }
            $this->order_log($order_info['id'],$order_info['member_id'],"用户支付订单",20);/*订单日志*/
            /*更新销售记录*/
            saller_order_log(0,$order_info['id'],0,$order_info['money_total'],$order_info['order_no']);
            $res4 = $this->demand->create_demand($order_info['id']);
            
            $receipt_address = json_decode($order_info['receipt_address'],true);
            
            $content = '订单支付成功了,订单号是：'.$order_info['order_no'].',客户名称：'.$receipt_address['full_name'].',服务地址是：'.$receipt_address['all_address'].',手机号是:'.$order_info['receipt_mobile'].',上门服务时间是:'.$order_info['door_time'];
            /*下单发送系统站内信*/
            $data = array('content'=>$content,'title'=>'订单支付成功了,订单号是:'.$order_info['order_no'],'send_name'=>'系统管理员');
            send_message_one(5,0,0,$data);
            
            if($res4){
                
            }else{
               return $res4; 
            }
            /*发送站内信*/
            //send_tips($member_id,'购买加油卡余额支付成功','订单号：'.$order_info['order_no'].'金额：'.$order_info['money_real']);
        } catch (\Exception $e) {
            $M->rollback();
            return $e->getMessage();
        }
        $M->commit();
        return true;
    }
    /**
     * 验证支付密码+余额
     * @param [type] $[member_info] [用户信息]
     * @param [type] $[order_info] [订单信息]
     * @return [type] [description]
     */
    public function check_password($member_info,$order_info){
        $deal_password=I('post.password');
        if(!$deal_password){
            throw new \Exception("请输入支付密码！", 1);
        }
        if(md5(md5($deal_password).$member_info['deal_salt']) != $member_info['deal_password']){
            throw new \Exception("支付密码错误！", 1);
        }
        /*验证用户余额是否足够支付*/
        if($member_info['balance']<$order_info['money_total']){
            throw new \Exception("余额不足！", 1);
        }
    }


    /**
     * 用户余额更新（余额）
     * @param [type] $[money] [余额]
     * @param [type] $[member_id] [用户ID]
     * @return [type] [description]
     */
    public function update_balance($money,$member_id){
        $data['id'] = $member_id;
        $data['balance']=$money;
        $res=update_data('member','','',$data);
        if(!is_numeric($res)){
            throw new \Exception("资金扣除异常", 1);
        }
    }

    /**
     * 资金记录
     * @param  $[data] [资金信息]
     * @return [type] [description]
     */
    public function money_recod($data){
        $data['deal_no']=build_order_no();
        $res=update_data('capital_record','','',$data);
        if(!is_numeric($res)){
            throw new \Exception("记录资金异常", 1);
        }
    }

    /**
     * 订单日志
     * @param  [type] $order_id  [订单ID]
     * @param  [type] $member_id [用户ID]
     * @param  [type] $remark    [备注信息]
     * @param  [type] $status    [订单状态]
     * @return [type]            [description]
     */
    public function order_log($order_id,$member_id,$remark,$status){
        $data['order_id']=$order_id;
        $data['member_id']=$member_id;
        $data['remark']=$remark;
        $data['status']=$status;
        $res=update_data('order_log','','',$data);
        if(!is_numeric($res)){
            throw new \Exception("记录日志异常", 1);
        }
    }

}