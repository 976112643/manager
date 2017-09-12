<?php
namespace Api\Controller\User;

use Common\Plugin\Pay\Pay;

class CapitalController extends IndexController
{
	protected $recharge_table = 'recharge_order';

	protected $capital_table = 'capital_record';

	/**
     * 生成充值订单
     */
    public function create_recharge_order()
    {
        $posts = I('post.');
        if( !$posts['money'] )      $this->set_error('充值金额不能为空');
        if( !$this->_id )      $this->set_error('请登录后再发布订单');
        if( $posts['money'] < 0 )      $this->set_error('充值金额无效');
        /**
         * 生成订单
         */
        $order_no = build_order_no();
        $data = array(
            'order_no' =>$order_no,
            'type' =>0, //订单类型 普通
            'money_total' => $posts['money'],   //订单总价
            'member_id' =>$this->_id,
            'status'=>'10',  //订单状态  未支付
            'remark' =>json_encode($posts)   //订单备注
        );
        $rule = array(
            array('money','1,999999','金额超出范围','0','between'),
        );
        /** 事物开始 */
        $m = M();
        try{

            $m->startTrans();

            $res = update_data($this->recharge_table,$rule,[],$data);
            if( !is_numeric($res)){
                throw new \Exception($res, 1);
            }
        }catch(\Exception $e){
            $m->rollback();
            $this->set_error($e->getMessage());
        }
        $m->commit();
        /** 返回所需数组 */
        $return_data = array(
            'order_no' =>$order_no
        );
        /** 如果提现类型为微信 */
        if( $posts['type'] == 'wx'){
            /** 生成微信预支付ID */
            $pay = Pay::getInstance('wx');
            $app_config = array(
                'openid' =>'',
                'body' =>'顺手APP充值',
                'order_no' =>$order_no,
                'money_total' => $posts['money'] * 100 ,
                'notify_url' =>U('/Api/Pay/Callback/weixin_recharge_callback',array(),true,true),
                'trade_type' =>'APP'
            );
            $pay_id = $pay->create_pay_id($app_config);
            $return_data['wx_pay_id'] = $pay_id ? $pay_id : "";
        }

        $this->set_success('生成成功',$return_data);
    }
    /**
     * 提现页面
     * @author 鲍海
     * @time 2017-03-29
     */
    public function get_withdraw_info(){
        $member_id = $this->_id;
        /*获取用户余额*/
        $member_info = get_info('member',array('id'=>$member_id),'deal_password,balance,weixin_open_id');
        /*判断是否有支付密码*/
        if($member_info['deal_password']){
            $info['is_have_pay_password'] = 1;
        }else{
            $info['is_have_pay_password'] = 0;
        }
        /** 每日提现次数 */
        $withdraw_num = C('WITHDRAW_NUM') ? C('WITHDRAW_NUM') : '3';
        /** 查询今日剩余次数 */
        $map = array(
        	'from_member_id' =>$member_id,
        	'add_time' => array(
        		array('egt',date('Y-m-d 00:00:00')),
        		array('elt',date('Y-m-d 23:59:59'))
        	),
        	'type' => array(
        		array('egt',41),
        		array('elt',42)
        	),
        );
        $count = count_data($this->capital_table,$map);
        $info['today_last_withdraw_num'] = $withdraw_num - $count;
        $info['balance'] = $member_info['balance'];
        /** 查询是否绑定微信 */
        $info['wx_open_id'] = $member_info['weixin_open_id'];
        /** 最低提现金额 */
        $info['lower_money'] = C('WITHDRAW_AMOUNT') ? C('WITHDRAW_AMOUNT') :'1.2';
        $this->set_success('ok',$info);
    }
    
     /**
     * 提现申请
     * 1、检查用户余额是否足够
     * 2、足够则从用户账户扣除提现金额
     * 3、生成提现资金记录
     *
     * @param int $member_id    提现用户ID
     * @param double $price 提现金额
     * @param int $bank_id
     * @param string $user_info_table   用户信息表(存储用户余额记录的表)
     * @param string $record_table  资金记录表
     * @param string $bank_model    用户银行卡模型
     * @return boolean  执行结果
     *
     * @author  李东<947714443@qq.com>
     * @date    2016-03-17
     */
    public function withdraw(){
        if( !IS_POST )      $this->set_error('你得POST提交数据啊！');

        /*数据验证*/
        $posts = I('post.');
        if( $posts['price'] <= 0 || !is_numeric($posts['price']) )  $this->set_error('提现金额有误');
        if( $posts['price'] < C('WITHDRAW_AMOUNT') )                $this->set_error('提现金额不能低于'.C('WITHDRAW_AMOUNT').'元');
        $check_type = array('1','2');
        if( !in_array($posts['type'], $check_type) )                $this->set_error('不支持的提现类型');
        if( $posts['type'] == '1'){
            //支付宝验证
            if( empty($posts['ali_account']) )                      $this->set_error('支付宝账号不能为空');
        }
        if( $posts['type'] == '2'){
            if( empty($posts['wx_open_id']) )                       $this->set_error('微信用户OPENID不能为空');
        }
        /** 支付密码，金额验证 */
        $member_info = get_info(D('MemberinfoView'),array('id'=>$this->_id),'deal_password,deal_salt,balance,nickname,mobile');
        $now_password = get_md5_password( $posts['pay_password'],$member_info['deal_salt']);

        if( $now_password != $member_info['deal_password'] )        $this->set_error('支付密码错误');
        if( $member_info['balance'] < $posts['price'])              $this->set_error('提现金额超出当前可提现额度');
        /** 每日提现次数 */
        $withdraw_num = C('WITHDRAW_NUM') ? C('WITHDRAW_NUM') : '3';
        /** 查询今日剩余次数 */
        $map = array(
            'from_member_id' =>  $this->_id,
            'add_time' => array(
                array('egt',date('Y-m-d 00:00:00')),
                array('elt',date('Y-m-d 23:59:59'))
            ),
            'type' => array(
                array('egt',41),
                array('elt',42)
            ),
        );
        $count = count_data($this->capital_table,$map);
        if( $withdraw_num - $count < 1){
            $this->set_error('已超出今日提现次数');
        }

        /** 如果提现的金额小于系统设定的额度，那么阻止他提现 */
        if( $posts['price'] < C('WITHDRAW_AMOUNT')){
            $money = sprintf("%.2f", $posts['price']-($posts['price']*C('WITHDRAW_FEE')));
            $money_fee = C('WITHDRAW_FEE');
        }else{
            $money = sprintf("%.2f", $posts['price']);
            $money_fee = '0';
        }

        if( $posts['type'] == '1'){
            $remark = array(
                'username'      => $member_info['nickname'],
                'bank'          =>'支付宝',
                'account'       =>$posts['ali_account']
            );
            $type = '42'; //支付宝提现
        }
        if( $posts['type'] == '2'){
            $remark = array(
                'username'      => $member_info['nickname'],
                'bank'          =>'微信',
                'account'       =>$posts['wx_open_id']
            );
            $type = '41'; //微信提现
            if( $money < 1 ){
                $this->set_error('扣除手续费后,本次提现'.$money.'不满足微信提现要求的最低1元');
            }
        }

        //开始事务
        $M=M();
        try {
            /*开始事务*/
            $M->startTrans();
            $order_no = build_order_no();

            /** 生成资金记录 */
            $data = array(
                'from_member_id'    => $this->_id,
                'to_member_id'      => '0',
                'type'              => $type,
                'order_no'          => $order_no,
                'money'             => $posts['price'],
                'charge'            => $money,
                'money_fee'         => $money_fee,
                'status'            => '41', //待处理
                'remark'            => json_encode($remark)
            );
            $result1 = update_data('capital_record',array(),array(),$data);
            $result2 = M('member')->where(['id' => $this->_id])->setDec('balance',$money);  //更新余额
            //$result3 = M('member_info')->where(['uid' => $this->_id])->setInc('integral',intval($money));   //更新积分
            //事务提交
            if( !is_numeric($result1) || !is_numeric($result2) ){
                throw new \Exception("系统错误", 1);
            }
        
        }catch (\Exception $e) {
            //事务回滚
            $M->rollback();
            $this->set_error('申请失败'.$e->getMessage());
        }
        $M->commit();
        $return_data = array(
            'balance' =>$member_info['balance'] - $money
        );
        $this->set_success('申请成功',$return_data);
    }
}

?>