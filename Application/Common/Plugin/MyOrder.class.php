<?php
namespace Common\Plugin;

use Common\Controller\ApiController;
use Common\Help\DateHelp;
use Common\Help\PushHelp;
/**
 * 我的任务管理类
 */
class MyOrder extends ApiController
{
	/**
     * 任务状态分组
     * @var array
     */
    public $status_code = array(
        '10'=>'待支付',
        '20'=>'已支付',
        '21'=>'申请退款',
        '22'=>'排队中',
        '30'=>'已接单', //进行中
        '40'=>'待评价',
        '45'=>'接单人已确认',
        '50'=>'任务完成',
        '90'=>'任务关闭',
        '91'=>'超期关闭',
        '99'=>'已取消',
        '100'=>'任务删除',
    );
    /** @var [type] [用户ID] */
    protected $_uid;

    protected $table = 'order';

    protected $model = 'OrderDetailView';

    protected $person;

    /**
     * 子类继承
     */
    protected function __autoload( $uid ){
    	$this->_uid = $uid;
    	$this->person = new Person();
    }
    /**
     * 接口-我发布的任务列表
     */
    public function my_send( $uid )
    {
    	$map = array(
    		'member_id' =>$uid
    	);
    	$field = 'id,order_no,detail_title,description,money_total,seller_mobile,add_time,status,is_img';
    	$res = $this->page(D($this->model),$map,'add_time desc,id desc',$field);
    	if( $res['list']){
    		$list = $this->select_img_list($res['list']);
            $list = $this->check_has_comment($list,$uid);
            unset($res['list']);
    	}
    	return $list;
    }
    /**
     * 接口-我接受的任务列表
     */
    public function my_receive( $uid )
    {
    	$map = array(
    		'seller_id' =>$uid
    	);
    	$field = 'id,order_no,detail_title,description,money_total,member_mobile,member_nickname,member_head_img,add_time,status,is_img';
    	$res = $this->page(D($this->model),$map,'add_time desc,id desc',$field);
    	if( $res['list']){
            array_walk($res['list'], function(&$a){
                $a['member_head_img'] = file_url($a['member_head_img']);
            });
            $list = $this->select_img_list($res['list']);
            $list = $this->check_has_comment($list,$uid,false);
            unset($res['list']);
    	}
    	return $list;
    }
    /**
     * 查询是否有图片
     * @param  array  $list [description]
     * @return [type]       [description]
     */
    protected function select_img_list($list = array())
    {
        $order_id = array();
        foreach ($list as $key => $v) {
            /** 查询是否有图片 */
            if($v['is_img']){
                $order_id[] = $v['id'];
            }
        }
        if($order_id){
            $img = get_result('order_image',array('order_id'=>array('in',$order_id)),'','order_id,image');
            /** 图片转成全路径 */
            $img_class = new \Common\Help\ImgHelp();
            array_walk($img, function(&$a) use($img_class){ 
                $_img = $a['image'];
                $a['image'] = file_url($_img);
                $a['thumb_image'] = show_member_head_img('',$img_class->app_thumb($_img,'950','950'));
            });
        }
        /** 合并原数据组 */
        foreach ($list as $k => $v) {
            $order_img = array();
            foreach ($img as $key => $value) {
                if($value['order_id'] == $v['id']){
                    unset($value['order_id']);
                    $order_img[] = $value;
                }
            }
            $list[$k]['order_img'] = $order_img;
        }
        return $list;
    }
    /**
     * 查询是否可以评价
     * @param  array  $list [description]
     * @return [type]       [description]
     */
    protected function check_has_comment($list = array(),$uid,$is_send = true)
    {
        $comment_id = array();
        foreach ($list as $key => $v) {
            /** 查询是否有评论 */
            if($is_send){
                /** 如果有接单人，那么就要查询是否可以评价，否则都是不可以评价的 */
                if( $v['seller_mobile'] && ($v['status'] != '30') ){
                    $comment_id[] = $v['id'];
                }
            }else{
                if($v['status'] > 30){
                    $comment_id[] = $v['id'];
                }
            }
            
        }
        /** 如果存在有评价的订单，就查出所有的订单号*/
        $comment = array();
        if($comment_id){
            $comment = get_result('order_comment',array('comment_id'=>$uid,'order_id'=>array('in',$comment_id)),'','order_id');
            $comment = $comment?array_column($comment,null,'order_id'):array();
        }
        /** 合并原数据组 */
        foreach ($list as $k => $v) {
            $is_has_comment = '1';
            if($is_send){
                /** 如果有接单人，那么就要查询是否可以评价，否则都是不可以评价的 */
                if( $v['seller_mobile'] && ($v['status'] != '30') ){
                    $is_has_comment = $comment[$v['id']] ? '1' :'0';
                }
            }else{
                if($v['status'] > 30){
                    $is_has_comment = $comment[$v['id']] ? '1' :'0';
                }
            }
            $list[$k]['is_has_comment'] = $is_has_comment;
        }
        return $list;
    }
    /**
     * 接口-我发布的任务详情
     */
    public function send_order_info( $order_id , $uid ,$status )
    {
    	$date = new DateHelp();
        $field = 'id,order_no,detail_title,description,money_total,longitude,latitude,status,is_img,door_time,cancel_content,seller_id,seller_nickname,seller_mobile,seller_head_img,seller_title_id,seller_start_rating,add_time';
        $info = get_info( D($this->model) ,array('id' =>$order_id),$field );
        if( !$info['id'] ) return array();
        $check_status = array('20','22');
        if( in_array($info['status'], $check_status) ){
            /** 获取排队中的人员列表 */
            $queue = D('OrderqueueView')->where(array('order_id'=>$info['id']))->select();
            if($queue){
                array_walk($queue, function(&$a){ $a['seller_head_img'] = file_url($a['seller_head_img']) ;});
            }
            $info['queue_list'] = $queue ? $queue : array();
        }else{
            $info['queue_list'] = array();
        }
        /** 获取接单用户头衔 */
        if( $info['seller_id'] ){
            $title = new Title();
            $title_info = $title->get_data_by_id($info['seller_title_id']);
            $info['seller_title'] = $title_info['name'];
        }else{
            $info['seller_title'] = '';
        }
        if( $info['seller_id'] && ($info['status'] != 30) ){
            /** 查询是否可以评价 */
            $count = count_data('order_comment',array('comment_id'=>$uid,'order_id'=>$order_id));
            $info['is_has_comment'] = $count ? '1' :'0'; //是否可以评价
        }else{
            $info['is_has_comment'] = '1';
        }
        

        $info['seller_head_img'] = file_url($info['seller_head_img']);  //接单人头像
        $info['last_time'] = $date->last_time(strtotime($info['door_time']),NOW_TIME,2); //倒计时
        $info['order_img'] = select_order_image($info['is_img'],$info['id']); //任务图片

    	return $info;
    }
    /**
     * 接口-我接受的任务详情
     */
    public function receive_order_info( $order_id , $uid ,$status )
    {
    	$date = new DateHelp();
        $field = 'id,order_no,detail_title,description,money_total,longitude,latitude,status,is_img,door_time,cancel_content,member_nickname,member_id,member_mobile,member_head_img,member_title_id,member_start_rating,add_time';
        $info = get_info( D($this->model) ,array('id' =>$order_id),$field );
        if( !$info['id'] ) return array();
        $title = new Title();
        $title_info = $title->get_data_by_id($info['member_title_id']);
        $info['member_title'] = $title_info['name'];   //发布人头衔
        $info['member_head_img'] = file_url($info['member_head_img']); //发布人头像

        $info['last_time'] = $date->last_time(strtotime($info['door_time']),NOW_TIME,2); //任务倒计时
        $info['order_img'] = select_order_image($info['is_img'],$info['id']);  //任务图片
        if( $info['status'] > 30){
            /** 查询是否可以评价 */
            $count = count_data('order_comment',array('comment_id'=>$uid,'order_id'=>$order_id));
            $info['is_has_comment'] = $count ? '1' :'0'; //是否可以评价
        }else{
            $info['is_has_comment'] = '1';
        }
        
    	return $info;
    }
    /**
     * 我发布的任务,选他了
     */
    public function select_us( $order_id , $uid ,$select_uid)
    {
        $m = M();
        
        try{
            $m->startTrans();
            $map = array(
                'id' =>$order_id
            );
            $info = get_info($this->table,$map,'id,member_id,detail_title,description');
            if( !$info['member_id'] ){
                throw new \Exception('任务不存在', 1);
            }
            if( $info['member_id'] != $uid ){
                throw new \Exception('当前任务发布人出错', 1);
            }
            /** 更新任务表状态 30 */
            $data = array(
                'id' =>$order_id,
                'status' =>30,
                'seller_id' =>$select_uid,
                'ship_time' =>date('Y-m-d H:i:s'),
                'confirm_time' =>date('Y-m-d H:i:s')
            );
            $res = update_data( $this->table,[],[],$data);
            if( !is_numeric($res) ){
                throw new \Exception('更新失败', 1);
            }
            /** 记录到任务日志中 */
            order_log($order_id,$uid,'0','用户将该任务分配给了'.$select_uid);

        }catch( \Exception $e){
            $m->rollback();
            return $e->getMessage();
        }
        $m->commit();
        /** 查询当前排队中的其他用户，通知他们该订单已被接受 */
        $search = array(
            'order_id'=>$order_id,
            'uid' =>array('neq',$select_uid)
        );
        $other_queue_list = get_result('order_queue',$search,'id asc','uid');
        if($other_queue_list){
            /** 通知其他人 */
            $info['type'] = 'queue';
            $info['status'] = 30;
            $info['title'] = '您申请接受的任务"'.$info['detail_title'].'"已经选择其他人完成了，去看看其他的吧！';
            $send_info = array(
               'id'=>array_column($other_queue_list,'uid'),    //[人员ID 或者'all']
               'title'=>$info['title'],
               'text' =>$info['description'],
               'content' =>$info,  //[推送通知内容]
               'type'=>'queue'     //和客户端协议的类型
            );
            $this->send_jg_notice($send_info);
        }

        /** 发送极光消息通知对方 */
        $info['type'] = 'receive';
        $info['status'] = 30;
        $send_info = array(
           'id'=>$select_uid,    //[人员ID 或者'all']
           'title'=>'恭喜您！申请的任务"'.$info['detail_title'].'"已成功被选中,请尽快完成',
           'text' =>$info['description'],
           'content' =>$info,  //[推送通知内容]
           'type'=>'receive'     //和客户端协议的类型
        );
        $this->send_jg_notice($send_info);
        return $res;
    }
    /**
     * 取消任务
     */
    public function cancel_order( $order_id , $uid ,$cancel_content)
    {
        $m = M();
        
        try{
            $m->startTrans();
            $map = array(
                'id' =>$order_id
            );
            $info = get_info($this->table,$map,'id,status,order_no,member_id,money_total,seller_id,detail_title,description,cancel_id');
            if( !$info['member_id'] ){
                throw new \Exception('任务不存在', 1);
            }
            if( $info['cancel_id'] ){
                throw new \Exception('当前任务已取消', 1);
            }
            /** 更新任务表状态 30 */
            $data = array(
                'id' =>$order_id,
                'status' =>99,
                'cancel_id' =>$uid,
                'cancel_content' => $cancel_content
            );
            $res = update_data( $this->table,[],[],$data);
            if( !is_numeric($res) ){
                throw new \Exception('更新失败', 1);
            }
            /** 记录到任务日志中 */
            order_log($order_id,$uid,'0','用户取消了该任务,原因：'.$cancel_content);
            if( $info['status'] >= 20){
                /** 退还任务金额到用户余额中 */
                $res = M('member')->where(array('id'=>$info['member_id']))->setInc('balance',$info['money_total']);
                if( !is_numeric($res) ){
                    throw new \Exception("退款任务金额失败", 1);
                }
                /** 记录到资金记录中 */
                $data = array(
                    'type' =>'24', //系统退款
                    'order_no' =>$info['order_no'],
                    'from_member_id' =>'0',
                    'to_member_id' =>$uid,
                    'money' =>$info['money_total'],
                    'status' =>90,
                    'charge' =>$info['money_total']
                );
                $res = update_data('capital_record',[],[],$data);
                if( !is_numeric($res) ){
                    throw new \Exception("资金记录失败", 1);
                }
            }
            
            

        }catch( \Exception $e){
            $m->rollback();
            return $e->getMessage();
        }
        $m->commit();
        if( $info['status'] >= '30'){
            $info['status'] = 99;
            /** 发送极光消息通知对方 */
            if( $info['member_id'] == $uid && $info['seller_id']){
                $info['type'] = 'receive';
                $send_info = array(
                   'id'=>$info['seller_id'],    //[人员ID 或者'all']
                   'title'=>'您接受的任务"'.$info['detail_title'].'"被取消了,点击查看！取消理由：'.$cancel_content,
                   'text' =>'取消理由：'.$cancel_content,
                   'content' =>$info,  //[推送通知内容]
                   'type'=>'receive'     //和客户端协议的类型
                );
            }
            if( $info['seller_id'] == $uid){
                $info['type'] = 'send';
                $send_info = array(
                   'id'=>$info['member_id'],    //[人员ID 或者'all']
                   'title'=>'您发布的任务"'.$info['detail_title'].'"被取消了,点击查看！取消理由：'.$cancel_content,
                   'text' =>'取消理由：'.$cancel_content,
                   'content' =>$info,  //[推送通知内容]
                   'type'=>'send'     //和客户端协议的类型
                );
            }
            
            $this->send_jg_notice($send_info);
        } 
        
        return $res;
    }
    /**
     * 接单人-确认完成
     */
    public function seller_complete_order( $order_id,$seller_id)
    {
        $m = M();
        
        try{
            $m->startTrans();
            $map = array(
                'id' =>$order_id
            );
            $info = get_info($this->table,$map,'id,member_id,seller_id,detail_title,description,status');
            if( !$info['member_id'] ){
                throw new \Exception('任务不存在', 1);
            }
            if( $info['seller_id'] != $seller_id ){
                throw new \Exception('您没有资格完成当前任务', 1);
            }
            if( $info['status'] != '30'){
                throw new \Exception('请按照流程完成任务', 1);
            }
            if( $info['status'] == '50' ){
                throw new \Exception('该任务已被发布人确认完成', 1);
            }
            /** 更新任务表状态 45 */
            $data = array(
                'id' =>$order_id,
                'status' =>45,
            );
            $res = update_data( $this->table,[],[],$data);
            if( !is_numeric($res) ){
                throw new \Exception('更新失败', 1);
            }
            /** 记录到任务日志中 */
            order_log($order_id,$seller_id,'0','用户完成了该任务');

        }catch( \Exception $e){
            $m->rollback();
            return $e->getMessage();
        }
        $m->commit();
        /** 发送极光消息通知对方 */
        $info['type'] = 'send';
        $info['status'] = 45;
        $send_info = array(
           'id'=>$info['member_id'],    //[人员ID 或者'all']
           'title'=>'您发布的任务"'.$info['detail_title'].'"已完成,点击查看！',
           'text' =>$info['description'],
           'content' =>$info,  //[推送通知内容]
           'type'=>'send'     //和客户端协议的类型
        );
        $this->send_jg_notice($send_info);
        return $res;
    }
    /**
     * 发布人-确认完成
     */
    public function member_complete_order( $order_id,$uid)
    {
        $m = M();
        try{
            $m->startTrans();
            $map = array(
                'id' =>$order_id
            );
            $info = get_info($this->table,$map,'id,order_no,member_id,seller_id,detail_title,description,money_total,status');
            if( !$info['member_id'] ){
                throw new \Exception('任务不存在', 1);
            }
            if( $info['member_id'] != $uid ){
                throw new \Exception('您没有资格完成当前任务', 1);
            }
            if( $info['status'] == '50'){
                throw new \Exception('该任务已经完成,请勿重复操作', 1);
            }
            // if( $info['status'] != '45' ){
            //     throw new \Exception('当前任务,接单人尚未完成', 1);
            // }

            /** 更新任务表状态 50 */
            $data = array(
                'id' =>$order_id,
                'status' =>50,
                'complete_time' =>date('Y-m-d H:i:s')
            );
            $res = update_data( $this->table,[],[],$data);
            if( !is_numeric($res) ){
                throw new \Exception('更新失败', 1);
            }
            /** 把任务的钱发送给接单人 */
            $rake = C('ORDER_RAKE');
            if( is_numeric($rake) && $rake > 0 ){
                /** 平台计算抽佣金额 */
                $total = $info['money_total'] - $info['money_total'] * $rake / 100;
                $total = round(sprintf("%.2f", $total),2);
            }else{
                $total = $info['money_total'];
            }

            M('member')->where(array('id'=>$info['seller_id']))->setInc('balance',$total);
            /** 记录到资金记录中 */
            $data = array(
                'type' =>'25',
                'order_no' =>$info['order_no'],
                'from_member_id' =>'0',
                'to_member_id' =>$info['seller_id'],
                'money' =>$info['money_total'],
                'status' =>50,
                'money_fee'=>$info['money_total'] - $total,
                'charge' =>$total
            );
            $res = update_data('capital_record',[],[],$data);
            if( !is_numeric($res) ){
                throw new \Exception("资金记录失败", 1);
            }
            /** 记录到任务日志中 */
            order_log($order_id,$uid,'0','用户确认，该任务交易成功');
            /** 记录发布用户积分 */
            $integral = new Integral($uid);
            $res = $integral->add_order_num($order_id);
            /** 记录接单用户积分 */
            $integral = new Integral($info['seller_id']);
            $res = $integral->add_order_num($order_id);
            /** 更新头衔信息 */
            $title = new Title();
            $title->update_user_title($info['seller_id']);

        }catch( \Exception $e){
            $m->rollback();
            return $e->getMessage();
        }
        $m->commit();
        /** 通知接单人 */
        $info['type'] = 'receive';
        $info['status'] = 50;
        $send_info = array(
           'id'=>$info['seller_id'],    //[人员ID 或者'all']
           'title'=>'您接受的任务"'.$info['detail_title'].'"已完成,点击查看！',
           'text' =>$info['description'],
           'content' =>$info,  //[推送通知内容]
           'type'=>'receive'     //和客户端协议的类型
        );
        $this->send_jg_notice($send_info);
        return $res;
    }
    /**
     * 任务推送
     */
    public function send_jg_notice($send_info)
    {
        $PushHelp = new PushHelp();
        $result = $PushHelp->Jg_push($send_info);
    }
}
?>