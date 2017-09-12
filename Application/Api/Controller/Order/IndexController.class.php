<?php
/**
 * 用户管理类
 */
namespace Api\Controller\Order;

use Api\Controller\Base\AuthController;
use Common\Help\Geohash;
use Common\Help\DateHelp;
use Common\Plugin\Pay\Pay;
use Common\Help\PushHelp;

class  IndexController extends AuthController
{
    
    /**
     * 用户ID
     */
    protected $member_id;
    
    /**
     * 用户任务表
     */
    protected $order = 'order';
    
    /**
     * 表名 任务详细表
     * @var string
     */
    protected $product_table='order_detail';
        
    /**
     * 子类集成
     * 
     * @return [type] [description]
     */
    protected function __init()
    {
        parent::__init();
        $this->member_id = $this->_id;
        
    }
    /**
     * 设置支付类型
     */
    protected $pay_type = array(
        array('id'=>'10','title' =>'支付宝支付'),
        array('id'=>'20','title' =>'微信支付'),
        array('id'=>'30','title' =>'余额支付'),
    );
    /**
     * 任务状态分组
     * @var array
     */
    public $status_code = array(
        '10'=>'待支付',
        '20'=>'已支付',
        '21'=>'申请退款',
        '22'=>'排队中',
        '30'=>'已接单',
        '40'=>'待评价',
        '45' =>'任务完成',
        '50'=>'交易完成',
        '90'=>'交易关闭',
        '91'=>'超期关闭',
        '99'=>'已取消',
        '100'=>'任务删除',
    );
    /**
     * 【用户端】发布任务
     * 1、接受任务参数    参数：分类ID，用户地址ID，上门时间，总价，用户ID
     * 2、分析参数是否合法
     * 3、生成用户任务
     * 4、返回任务ID
     * @return [type] [description]
     */
    public function create_order()
    {
        $posts = I('post.');
        if( !$posts['address'] )      $this->set_error('请选择上门服务地址');
        if( !$posts['longitude'] )      $this->set_error('经度不能为空');
        if( !$posts['latitude'] )      $this->set_error('纬度不能为空');
        if( !$posts['door_time'] )      $this->set_error('请选择有效时间');
        if( !$posts['money'] )      $this->set_error('赏金不能为空');
        if( !$posts['title'] )      $this->set_error('任务标题不能为空');
        if( !$posts['description'] )      $this->set_error('任务描述不能为空');
        if( !$this->member_id )      $this->set_error('请登录后再发布任务');
        if( !$posts['mobile'] )      $this->set_error('联系方式不能为空');
        if( $posts['money'] < 0 )      $this->set_error('赏金参数无效');
        /** 查询赏金是否符合要求 */
        $fee = C('ORDER_FEE') ? C('ORDER_FEE') : '5';
        if( $posts['money'] < $fee ) $this->set_error('赏金不可低于'.$fee.'元');

        /**
         * 查询上门时间是否合法
         */
        $now_time = time();
        $door_time = strtotime($posts['door_time']);
        if( $door_time <= $now_time )     $this->set_error('有效时间不得小于当前时间，请重新选择');
        $seven_time = $now_time + ( 7 * 86400);
        if( $door_time > $seven_time )      $this->set_error('有效时间不得超出当前时间7天,请重新选择');
        /**生成地图hash */
        $geo = new Geohash();
        $hash = $geo->encode($posts['latitude'],$posts['longitude']);
        /**
         * 生成任务
         */
        $order_no = build_order_no();
        $data = array(
            'order_no' =>$order_no,
            'type' =>0, //任务类型 普通
            'detail_title' =>$posts['title'],  //任务标题
            'description' =>$posts['description'], //任务描述
            'money_total' => $posts['money'],   //任务总价
            //'money_total' => 0.01,   //TODO 测试阶段价格固定
            'receipt_mobile' =>$posts['mobile'],   //收货电话
            'receipt_address' =>$posts['address'],  //收货地址信息
            'member_id' =>$this->member_id,
            'status'=>'10',  //任务状态  未支付
            'door_time'=>$posts['door_time'],   //上门时间
            'longitude' =>$posts['longitude'], //经度
            'latitude' => $posts['latitude'], //维度
            'geo_hash' =>$hash,
            'remark' =>json_encode($posts)   //任务备注
        );
        /** 判断是否有图片 */
        $num = count( $_FILES[ 'img' ]['name'] );
        if( $num > 0){
           $data['is_img'] = '1';
        }
        $rule = array(
            array('detail_title','1,15','任务标题不能超过15个字','0','length'),
            array('description','1,150','任务描述不能超过150个字','0','length'),
            array('address','1,200','地址不能超过200个字','0','length'),
            array('longitude','1,30','经度不合法','0','length'),
            array('latitude','1,30','维度不合法','0','length'),
            array('money','1,999999','金额超出范围','0','between'),
            array('mobile',MOBILE,'手机号不合法'),
        );
        /** 事物开始 */
        $m = M();
        try{

            $m->startTrans();

            $res = update_data($this->order,$rule,[],$data);
            if(is_numeric($res)){
                /** 判断是否上传了图片 */
                if( $data['is_img'] == '1'){
                    $info = api_upload_picture('img','Uploads/Order/');
                    if( is_array($info) ){
                        if( $info['0'] ){
                            $img = $info;
                        }else{
                            $img = array($info);
                        }
                        $data = array();
                        foreach($img as $file){  
                            $data[] = array(
                                'order_id' =>$res,
                                'image'      =>$file['savepath'].$file['savename'],
                                'add_time'=>date('Y-m-d H:i:s'),
                            );
                        }
                        $_res = M('order_image')->addAll($data);
                    }
                    
                }
                /** 发送消息到后台 */
                $return_data = array(
                        'order_id' =>$res
                );
                // $content = '来任务了,任务号是：'.$order_no.',手机号是:'.$posts['mobile'].',有效时间是:'.$posts['door_time'];
                // /*下单发送系统站内信*/
                // $data = array('content'=>$content,'title'=>'又来新任务了,金额是:￥'.$posts['money'],'send_name'=>'系统管理员');
                // send_message_one(4,0,0,$data);
                /** 记录到任务日志中 */
                order_log($res,$this->member_id,0,'用户发布任务');
            }else{
                throw new \Exception($res, 1);
            }
        }catch(\Exception $e){
            $m->rollback();
            $this->set_error($e->getMessage());
        }
        $m->commit();
        $this->set_success('发布任务成功',$return_data);
    }
    
    /**
     * 获取微信预支付ID
     * @author 鲍海
     * @time 2017-03-25
     */
    public function get_wxpay_id(){
        $data = I('post.');
        $order_type = array('order','recharge');
        if( !$data['order_id'] || !is_numeric($data['order_id']) )  $this->set_error('任务ID不能为空'); 
        if( !$data['order_type'] || !in_array( $data['order_type'] ,$order_type ) ) $this->set_error('任务类型错误');
        $table = $data['order_type'] =='order' ? 'order':'recharge_order';
        if( $data['order_type'] == 'order'){
            $table = 'order';
            $notify_url = U('Api/Pay/callback/weixin_callback',array(),true,true); //微信异步回调信息
        }else{
            $table = 'recharge_order';
            $notify_url = U('Api/Pay/callback/weixin_recharge_callback',array(),true,true); //微信充值异步回调信息
        }

        //获取任务数据
        $order_info = get_info($table,array('id'=>$data['order_id'],'status'=>10));
        if(!$order_info['id']){
             $this->set_error('任务已支付,或没有此任务');
        }
        $wechat = Pay::getInstance('wx');
        $config = array(
            'openid'    =>'',
            'body'      =>'任务支付',
            'order_no'  =>$order_info['order_no'],
            'money_total' =>$order_info['money_total'] * 100,
            'notify_url' =>$notify_url,
            'trade_type' =>'APP'
        );
        $payId = $wechat->create_pay_id( $config );
        if( $payId ){
            $this->set_success('ok',array('pay_id'=>$payId));
        }else{
            $this->set_error('预支付ID生成失败');
        }
        
    }

    /**
     * 【用户端】发布任务后的跳转支付
     */
    public function get_pay_config()
    {
        $order_id = I('get.order_id');
        if(!is_numeric($order_id) || !$order_id)  $this->set_error('非法参数');

        $field = 'id,order_no,money_total,detail_title,description';
        $order_info = get_info($this->order,array('id'=>$order_id,'status'=>'10'),$field);
        if(!$order_info)    $this->set_error('任务异常');
        $return_data = array(
            'order_no'=>$order_info['order_no'],//任务号
            'money' =>$order_info['money_total'],//任务金额
            'title' =>$order_info['detail_title'],  //任务标题
            'description' =>$order_info['description']//任务描述
        );
        /*获取用户是否有支付密码*/
        $member_info = get_info('member',array('id'=>$this->member_id));
        if($member_info['deal_password']){
            $return_data['is_have_pay_password'] = 1;
        }else{
            $return_data['is_have_pay_password'] = 0;
        }
        $pay = $this->pay_type;
        foreach ($pay as $key => $value) {
            switch ($value['id']) {
                case '10':
                    $url = '/Api/Pay/callback/ali_callback'; //支付宝支付回调地址
                    break;
                case '20':
                    $url = '/Api/Pay/callback/weixin_callback'; //微信支付回调地址
                    break;
                case '30':
                    $url = '/Api/Pay/callback/default_callback'; //余额支付回调地址
                    break;
                default:
                    # code...
                    break;
            }
            $pay[$key]['callback_url'] = U($url,array(),true,true);
        }
        $return_data['pay_config'] = $pay;
        $return_data['balance'] = $member_info['balance'];
        $this->set_success('ok',$return_data);
    }


    /**
     * 任务确认页
     * @author 鲍海
     * @time 2017-03-24
     */
    public function pay_order_info(){
        /*查询最新任务*/
        $member_id = $this->member_id;
        $map = [];
        $map['member_id'] = $member_id;
        $map['id'] = I('order_id');
        $map['status'] = 10;
        $order_info = get_info('order',$map,true,'add_time desc');
        if(!$order_info['id']){
           $this->set_error('没有此任务，或任务已支付');  
        }
        /*组合任务数据*/
        $order_info = $this->pick_char($order_info,'id,order_no,money_total');
        $pay = $this->pay_type;
        foreach ($pay as $key => $value) {
            switch ($value['id']) {
                case '10':
                    $url = '/Api/Pay/callback/ali_callback'; //支付宝支付回调地址
                    break;
                case '20':
                    $url = '/Api/Pay/callback/weixin_callback'; //微信支付回调地址
                    break;
                case '30':
                    $url = '/Api/Pay/callback/default_callback'; //余额支付回调地址
                    break;
                default:
                    # code...
                    break;
            }
            $pay[$key]['callback_url'] = $url;
        }
        $order_info['pay_config'] = $pay;
        
        /*获取用户是否有支付密码*/
        $member_info = get_info('member',array('id'=>$this->member_id));
        if($member_info['deal_password']){
            $order_info['is_have_pay_password'] = 1;
        }else{
            $order_info['is_have_pay_password'] = 0;
        }
        
        $this->set_success('ok',$order_info);          
    }

    /**
     * 任务-任务详细
     * @author 鲍海
     * @time 2017-03-21 
     */
    public function order_info(){
        $order_id = I('order_id');
        if( !$order_id ) $this->set_error('任务ID不能为空');
        /** 获取任务详细信息 */
        $order_info = $this->get_order_details( $order_id );
        
        /** 是否已举报过 */
        $data = array(
            'order_id' =>$order_id,
            'uid' =>$this->_id
        );
        /** 查询是否已举报过 */
        $report_info = get_info('order_report',$data);
        $order_info['is_report'] = $report_info['id'] ? '1':'0';
        /** 查询是否已申请接受过 */
        $receive_info = get_info('order_queue',$data);
        $order_info['is_receive'] = $receive_info['id'] ? '1':'0';
        /** 查询是否是自己发布的任务 */
        $order_info['is_my_send'] = ($order_info['info']['member_id'] == $this->_id) ? '1' : '0';

        unset($order_info['info']);
        unset($order_info['info']['member_id']);
        $this->set_success('ok',$order_info); 
    }
    /**
     * 任务-举报
     */
    public function report()
    {
        $posts = I('post.');
        if( !$posts['order_id'] || !is_numeric($posts['order_id']) ) $this->set_error('任务ID不能为空');
        $data = array(
            'order_id' =>$posts['order_id'],
            'uid' =>$this->_id
        );
        /** 查询该订单是否是举报人的 */
        $info = get_info('order',array('id'=>$posts['order_id'],'member_id'=>$this->_id));
        if($info ){
            $this->set_error('您不能举报自己的任务');
        }
        /** 查询是否已举报过 */
        $info = get_info('order_report',$data);
        if( $info['id'] ) $this->set_error('您已举报过');


        $res = update_data('order_report',[],[],$data);
        if( is_numeric($res) ) {
            $this->set_success('举报成功',$res);
        }else{
            $this->set_error('举报失败',$res);
        }
    }
    /**
     * 任务-接受
     */
    public function receive()
    {
        $posts = I('post.');
        if( !$posts['order_id'] || !is_numeric($posts['order_id']) ) $this->set_error('任务ID不能为空');
        $data = array(
            'order_id' =>$posts['order_id'],
            'uid' =>$this->_id
        );
        /** 查询任务是否是当前人员发布的 */
        $order_info = get_info('order',array('id'=>$posts['order_id']),'id,member_id,detail_title,status');
        if( $order_info['member_id'] == $this->_id ) $this->set_error('您不能接受自己发布的任务');
        /** 查询接单人是否已经绑定了手机号 */
        $member_info = get_info('member',array('id'=>$this->_id),'mobile');
        if( !$member_info['mobile'] )  $this->set_error('请先绑定手机号');
        /** 查询是否已申请过 */
        $info = get_info('order_queue',$data);
        if( $info['id'] ) $this->set_error('您已申请接受过了');
        
        $model = M();
        $model->startTrans();
        try{
            /** 更新任务状态为 22 排队中 */
            $res = update_data('order',[],array('id'=>$posts['order_id']),array('status'=>'22'));
            if( !is_numeric($res)){
                throw new \Exception("数据库异常", 1);
            }
            /** 插入排队中 */
            $res = update_data('order_queue',[],[],$data);
            if( !is_numeric($res)){
                throw new \Exception("申请失败", 1);
            }
            /** 记录订单日志 */
            order_log($posts['order_id'],$this->_id,0,'用户申请接受该任务');
            /** 推送极光消息 */
            $push = new PushHelp();
            $order_info['status'] = '22';
            $order_info['type'] = 'send';
            $info = array(
                'id' => $order_info['member_id'],
                'title' => '您发布的任务"'.$order_info['detail_title'].'"已有人申请接单',
                'text' => '您发布的任务"'.$order_info['detail_title'].'"已有人申请接单,点我去查看',
                'content' => $order_info,
                'type' => 'send',
            );
            $push->Jg_push($info);
        }catch (\Exception $e){
            $model->rollback();
            $this->set_error($e->getMessage());
        }
        $model->commit();
        $this->set_success('申请成功',$res);
    }
    /**
     * 获取任务的详细内容-公共使用
     * @param  [type] $order_id [description]
     * @return [type]           [description]
     */
    public function get_order_details( $order_id )
    {
        /** 查询任务信息 */
        $map['is_del']=array('in',[0,2]);
        $map['id'] = $order_id;
        $field='id,status,member_id,order_no,add_time,money_total,seller_id,door_time,receipt_address,receipt_mobile,longitude,latitude,is_img,detail_title,description,is_img';
        $info=get_info(D('order'),$map,$field,'add_time desc');
        if(!$info['id']){ $this->set_error('没有此任务'); }

        $order_info = array();
        /*我的收货地址信息*/
        $receipt_address['mobile'] = $info['receipt_mobile'];
        $receipt_address['address'] = $info['receipt_address'];
        $receipt_address['longitude'] = $info['longitude'];
        $receipt_address['latitude'] = $info['latitude'];
        $order_info['my_info'] = $receipt_address; 
        /** 剩余时间*/
        $date = new DateHelp();
        $day = $date->last_time(strtotime($info['door_time']),NOW_TIME,2);
        $order_info['last_time'] = $day;
        /** 任务信息 */
        $order_data['order_no'] = $info['order_no']; 
        $order_data['money'] = $info['money_total'];
        $order_data['add_time'] = $info['add_time']; 
        $order_data['door_time'] = $info['door_time'];
        $order_data['confirm_time'] = $info['confirm_time']; 
        $order_data['detail_title'] = $this->remove_sensitive($info['detail_title']); 
        $order_data['description'] = $this->remove_sensitive($info['description']); 
        /** 是否有图片 */
        $order_info['order_image'] = select_order_image($info['is_img'],$info['id']);
        $api_order_code = $this->api_order_code();
        if($api_order_code[$info['status']]!=''){
            $order_data['status_text'] = $api_order_code[$info['status']];
        }else{
            $order_data['status_text'] = null;
        }
        $order_info['order_info'] = $order_data;
        $order_info['info'] = $info;
        return $order_info;
    }
    /**
     * 获取任务赏金价格
     */
    public function get_order_free()
    {
        $free = C('ORDER_FEE') ? C('ORDER_FEE') : '5';
        $this->set_success('ok',array('free'=>$free));
    }

    
    /**
     * 任务操作
     * @return [type] [description]
     */
    public function api_order_code(){
        return array(
            '10'=>array(
                'title'=>'待支付',
                'status'=>'10',
                'child'=>array(
                    array(
                        'title'=>'去支付',
                        'status'=>'20',
                    ),
                    array(
                        'title'=>'取消任务',
                        'status'=>'99',
                    ),
                ),
            ),
            '20'=>array(
                'title'=>'已支付',
                'child'=>array(
                    array(
                        'title'=>'申请退款',
                        'status'=>'21',
                    )
                ),
            ),
            '21'=>array(
                'title'=>'申请退款',
                'child'=>array(
                    array(
                        'title'=>'删除任务',
                        'status'=>'100',
                    )
                ),
            ),
            '30'=>array(
                'title'=>'已接单',
                /*'child'=>array(
                    array(
                        'title'=>'删除任务',
                        'status'=>'0',
                    ),
                ),*/
            ),
            
            '40'=>array(
                'title'=>'已完成',
                'child'=>array(
                    array(
                        'title'=>'去评论',
                        'status'=>'50',
                    ),
                ),
            ),
            '50'=>array(
                'title'=>'交易完成',
                'child'=>array(
                    array(
                        'title'=>'删除任务',
                        'status'=>'100',
                    ),
                ),
            ),
            '90'=>array(
                'title'=>'交易关闭',
                'child'=>array(
                    array(
                        'title'=>'删除任务',
                        'status'=>'100',
                    ),
                ),
            ),
            '91'=>array(
                'title'=>'超期关闭',
                'child'=>array(
                    array(
                        'title'=>'删除任务',
                        'status'=>'100',
                    ),
                ),
            ),
            '99'=>array(
                'title'=>'已取消',
                'child'=>array(
                    array(
                        'title'=>'删除任务',
                        'status'=>'100',
                    ),
                ),
            ),
            '-1'=>array(
                'title'=>'未接单',
            ),
        );
    }
    
    
    
    
}
?>