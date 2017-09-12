<?php  
namespace Common\Controller\CertificationOrder;
use Api\Controller\Base\BaseController;
/**
 * 加油卡支付主类
 * @author   鲍海
 * @time     2016.12.20
 */
class CertificationPayController extends BaseController{
    /**
     * 自动加载
     * @return [type] [description]
     */
    public function __autoload(){
        parent::__autoload();
    }

    /**
     * 微信回调处理方法
     * 
     * @author  李东<947714443@qq.com>
     * @date    2016-04-20
     */
    public function wxnotify(){
        define('WXAPP', 2);
        /** 日志文件*/
        vendor('Wxpay.WxPay#Log');
        $log_file = dirname(C('LOG_PATH')).'/Pay/WeiXin/';
        $log_file .= date('y_m_d').'.log';
        $logHandler= new \CLogFileHandler($log_file);
        $log = \Log::Init($logHandler, 15);
        \Log::DEBUG("begin notify");
        /** 微信支付类引入*/
        vendor('Wxpay.WxPay#PayNotifyCallBack');
        // \WxPayConfig::$config = C('payment.wxpay');
        
        $PayNotify = new \PayNotifyCallBack();
        /** 验证签名，获取返回数据*/
        $data = $PayNotify->Handle(false);
        
        \Log::DEBUG("call back:" . json_encode($data));
        if(!is_array($data)) {
            $PayNotify->NotifyReturn(false);
            return ;
        }
        $msg = 'OK';
        if(!$PayNotify->NotifyProcess($data, $msg)) {
            $PayNotify->NotifyReturn(false);
            return ;
        }
        
        $param=json_decode($data['attach'],true);
        $map['order_no']=$data['out_trade_no'];
        $map['status']=10;
        $order_info = get_info('fuel_card_order', $map);

        if($order_info){
            if(($data['total_fee']/100)!=$order_info['money_real']){
                $temp['member_id'] = $param['member_id'];
                $temp['order_no'] = $data['out_trade_no'];
                $temp['money'] = $data['total_fee']/100;
                $temp['type'] = 43;
                $temp['remark'] = json_encode($data);
                $temp['add_time'] = date('Y-m-d H:i:s');
                $flag = update_data('capital_error', [], [], $temp);
                $PayNotify->NotifyReturn(false);
                return ;
            }
            $result = $this->funds_order($order_info,$data['transaction_id'],$data['time_end'],2);
            
            if($result===true) {
                $PayNotify->NotifyReturn(true, false);
                return $result;
            }else{
                $PayNotify->NotifyReturn(false);
                return ;
            }
        }else{
            $PayNotify->NotifyReturn(false);
            return ;
        }
    }
    
    /**
     * 支付宝回调处理方法
     * @author  李东<947714443@qq.com>
     * @date    2016-04-17
     */
    public function alinotify(){
        $data = I();
        if(!$data){
            exit('缺少信息');
        }
        \Think\Log::write('加油卡支付宝回调');
        \Think\Log::write(serialize($data));
        $Pay = new \Think\Pay('alipay', C('payment.alipay'));
        $result = $Pay->verifyNotify($data);
        \Think\Log::write('加油卡支付宝验签');
        \Think\Log::write(serialize($result));
        if(!$result) {
            $this->apiReturn(array('msg' =>'异常错误'));
        }
        $info = $Pay->getInfo();
        \Think\Log::write(serialize($info));
        if(!$info['status']) {
            $this->apiReturn(array('msg' =>'支付失败'));
        }
        $order_info = get_info('fuel_card_order',['order_no' => $data['out_trade_no'],'status'=>10]);
        \Think\Log::write(serialize($order_info));
        if($order_info){
            if($data['total_fee']!=$order_info['money_real']){
                $temp['member_id'] = $order_info['member_id'];
                $temp['order_no'] = $data['out_trade_no'];
                $temp['money_real'] = $data['total_fee'];
                $temp['type'] = 44; //43 加油卡微信 44 加油卡支付宝 45 加油微信 46 加油支付宝 47 加油卡充值微信 48 加油卡充值支付宝 
                $temp['remark'] = json_encode($data);
                $temp['add_time'] = date('Y-m-d H:i:s');
                update_data('capital_error', [], [], $temp);
                \Think\Log::write(serialize($temp));
                \Think\Log::write('支付异常');
                $this->apiReturn(array('msg' =>'支付异常'));
            }
            $res = $this->funds_order($order_info, $data['trade_no'],$data['notify_time'],1);
            if($res===true) {
                $Pay->notifySuccess();
            }else{
                $this->apiReturn(array('status'=>'0','msg' =>$res));
            }
        }else{
            $this->apiReturn(array('msg' =>'支付异常')); 
        }
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
        $keys = I('post.user_key','','trim')?I('post.user_key','','trim'):'MDAwMDAwMDAwMIS3hWiEjY5nfaeHZ4K5cWSvnnJ2';
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
            $order_info = get_info('member_certification_order',$map);
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
                'money' => $order_info['money_real'],
                'remark'=> '余额支付资质认证',
                'status' => 20,
            );
            $money=$member_info['balance']-$order_info['money_real'];
            $this->update_balance($money,$member_id);/*用户减去余额*/
            $this->money_recod($data);/*资金记录*/

            $data=array(
                'pay_time'=>date('Y-m-d H:i:s'),
                'status' =>20,
            );
            $res=update_data('member_certification_order','',['id'=>$order_info['id']],$data);
            if(!is_numeric($res)){
                throw new \Exception("订单信息更新异常", 1);
            }
            /*更新用户资质认证缴费状态*/
            $this->update_member_fuel_card($order_info['id'],$member_id);/*更新用户资质认证缴费状态*/
            $this->order_log($order_info['id'],$order_info['member_id'],"用户支付订单",20);/*订单日志*/
            
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
     * 支付成功激活用户加油卡
     * @param [type] $[order_id] [订单ID]
     * @param [type] $[member_id] [用户ID]
     * @author  鲍海
     * @time    2016.12.21
     */
    public function update_member_fuel_card($order_id,$member_id){
        /**获取用户对应订单的加油卡*/
        $result = get_result('member_certification_order',array('member_id'=>$member_id,'order_id'=>$order_id));
        $ids = array();
        foreach($result as $row){
            $ids[] = $row['certification_id'];
        }
        /*批量更新数据*/
        $_POST = ['balance_status' => 1];
        $map ['certification_id'] = array ('in',$ids );
        $res = update_data('member_certification',[],$map);
        
        if(!is_numeric($res)){
            throw new \Exception("资质认证缴费失败", 1);
        }
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
        if($member_info['balance']<$order_info['money_real']){
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
     * 修改订单状态
     * @param  $[order_id] [订单ID]
     * @param  $[status] [状态值]
     * @return [type] [description]
     */
    public function update_order_status($order_id,$status){
        $res=update_data('fuel_card_order','',['id'=>$order_id],['status'=>$status]);
        if(!is_numeric($res)){
            throw new \Exception("订单状态更新异常", 1);
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
        $res=update_data('member_certification_order_log','','',$data);
        if(!is_numeric($res)){
            throw new \Exception("记录日志异常", 1);
        }
    }
    /**
     * 用户取消订单(未支付)
     * @return [type] [description]
     */
    public function cancel_order(){
        $order_id = intval(I('post.order_id'));
        $member_id = intval(I('post.member_id'));
        $map['id'] = $order_id;
        $map['status'] = 10;
        $map['member_id'] = $member_id;  
        try {
            $M=M();
            $M->startTrans();
            $order_info = get_info('fuel_card_order',$map);
            if(!$order_info) {
                throw new \Exception("无此订单！", 1);
            }
            $this->update_order_status($order_info['id'],100);/*订单状态更新*/
            $this->order_log($order_info['id'],$order_info['member_id'],"用户取消订单",100);/*订单日志*/
            /*发送站内信*/
            send_tips($member_id,'加油补贴-取消订单','订单号：'.$order_info['order_no'].'金额：'.$order_info['money_real']);
        } catch (\Exception $e) {
            $M->rollback();
            return $e->getMessage();
        }
        $M->commit();
        return true;  
    }

    /**
     * 用户退款（已支付）
     * @return [type] [description]
     */
    public function refund(){
        $order_id = intval(I('post.order_id'));
        $member_id = intval(I('post.member_id'));
        $map['id'] = $order_id;
        $map['status'] = 20;
        $map['member_id'] = $member_id;  
        try {
            $M=M();
            $M->startTrans();
            $order_info = get_info('order',$map);
            if(!$order_info) {
                throw new \Exception("无此订单！", 1);
            }
            $member_info = $this->check_member($member_id);
            $money=$member_info['balance']+$order_info['money_real'];
            $this->update_balance($money,$member_id);/*用户减去余额*/
            $this->update_order_status($order_info['id'],30);/*订单状态更新*/
            $this->update_order_detail_status($order_info['id'],30);/*订单状态更新*/
            $this->order_log($order_info['id'],$order_info['member_id'],"用户退款",30);/*订单日志*/
        } catch (\Exception $e) {
            $M->rollback();
            return $e->getMessage();
        }
        $M->commit();
        return true;  
    }

    /**
     * 删除订单
     * @author  鲍海
     * @time    2017.1.9
     */
    public function del_order(){
        $order_id = intval(I('post.order_id'));
        $member_id = intval(I('post.member_id'));
        $map['id'] = $order_id;
        $map['status'] = array('in',[100,40,20,50]);
        $map['member_id'] = $member_id;     
        try {
            $M=M();
            $M->startTrans();
            /*验证订单信息*/
            $order_info = get_info('fuel_card_order',$map);
            if(!$order_info) {
                throw new \Exception("此订单还未支付无法删除！", 1);
            }
            /*验证用户信息*/
            $member_info = $this->check_member($member_id);
            $data['is_del']=1;
            if($order_info['is_del']=='2'){
                $data['is_del']=3;
            }
            
            /*更新订单信息*/
            $res=update_data('fuel_card_order','',['id'=>$order_info['id']],$data);
            if(!is_numeric($res)){
                throw new \Exception("订单信息更新异常", 1);
            }
            /*记录订单日志*/
             $this->order_log($order_id,$member_id,"用户删除订单",101);/*订单日志*/
            /*发送站内信*/
            // send_tips($member_id,'美容保养订单被删除','订单号：'.$order_info['order_no']);
        } catch (\Exception $e) {
            $M->rollback();
            return $e->getMessage();
        }
        $M->commit();
        return true;
    }

}