<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/9
 * Time: 20:46
 */
namespace Api\Controller\Order;

use Api\Controller\User\IndexController;
use Api\Controller\User\EvaluationController;
use Common\Plugin\MyOrder;

/**
 * 任务-用户信息主类
 * @package Api\Controller\User
 */
class UserController extends IndexController
{
    protected $my_order;

    protected function __init()
    {
        parent::__init();
        /** @var MyOrder [我的订单管理类] */
        $this->my_order = new MyOrder();
    }
    /**
     * 任务-获取用户基础信息
     */
    public function get_user_base_info()
    {
        $user_id = I('user_id','0','int');
        if( !$user_id ) $this->set_error('用户ID不能为空');
        $info = $this->person->person_info($user_id);
        $info['start_rating'] = sprintf("%.1f",$info['start_rating']);
        $info = $this->pick_char($info,'uid,nickname,head_img,title,start_rating,gender');
        $this->set_success('ok',$info);
    }
    /**
     * 任务-获取用户个性信息
     */
    public function get_user_extend_info()
    {
        $user_id = I('user_id','0','int');
        if( !$user_id ) $this->set_error('用户ID不能为空');
        $info = $this->person->person_info($user_id);
        $info = $this->pick_char($info,'signature,age,constellation,skill,hobbies,constellation');
        $info['title_count'] = count( $this->title->get_user_all_list( $user_id ));
        $this->set_success('ok',$info);
    }
    /**
     * 任务-获取展示相册9张图片
     */
    public function get_user_view_photo()
    {
        $user_id = I('user_id','0','int');
        if( !$user_id ) $this->set_error('用户ID不能为空');
        $res = $this->personPhoto->my_photo_views($user_id);
        $res = $res ? $res :array();
        $this->set_success('ok',array('photo_list'=>$res));
    }
    /**
     * 任务-获取用户的评价
     */
    public function get_user_evaluation()
    {
        $user_id = I('user_id','0','int');
        if( !$user_id ) $this->set_error('用户ID不能为空');
        $map = array(
            'member_id' =>$user_id
        );
        $field = 'id,star_rating,order_id,seller_nickname,s_head_img,add_time,detail_title,content,tag_id,is_img,is_anonymous';  
        /** 获取人员收到的评价 */
        $Evaluation = new EvaluationController();
        $Evaluation->get_my_evaluation($map , $field);
    }
    /**
     * 我的任务-我发布的任务列表
     */
    public function my_send_order_list()
    {
        $res = $this->my_order->my_send( $this->_id );
        if( $res ){
            $this->set_success('ok',$res);
        }else{
            $this->set_error('暂无数据');
        }
    }
    /**
     * 我的任务-我接受的任务列表
     */
    public function my_receive_order_list()
    {
        $res = $this->my_order->my_receive( $this->_id );
        if( $res ){
            $this->set_success('ok',$res);
        }else{
            $this->set_error('暂无数据');
        }
    }
    /**
     * 我的任务-我发布的任务详情
     */
    public function my_send_order_details()
    {
        $order_id = I('order_id');
        $status = I('order_status');
        if( !$order_id ) $this->set_error('订单ID不能为空');
        if( !$status || !is_numeric($status) ) $this->set_error('订单状态不能为空');

        $order_info = $this->my_order->send_order_info( $order_id , $this->_id ,$status );
        if( $order_info ){
            $this->set_success('ok',$order_info);
        }else{
            $this->set_error('不存在的订单');
        }
        
    }
    /**
     * 我的任务-选他了
     */
    public function my_send_order_select_us()
    {
        if( !IS_POST ) $this->set_error('我需要你POST提交数据');
        $posts = I('post.');
        if( !$posts['order_id'] ) $this->set_error('你需要提交订单ID');
        if( !$posts['select_uid'] ) $this->set_error('你需要提交选中的用户ID');
        if( $posts['select_uid'] == $this->_id ) $this->set_error('您不能选择自己');

        $res = $this->my_order->select_us( $posts['order_id'] , $this->_id ,$posts['select_uid'] );
        if( is_numeric($res) ){
            $this->set_success('选择成功',$res);
        }else{
            $this->set_error($res);
        }
    }
    /**
     * 我的任务-我接受的任务详情
     */
    public function my_receive_order_details()
    {
        $order_id = I('order_id');
        $status = I('order_status');
        if( !$order_id ) $this->set_error('订单ID不能为空');
        if( !$status || !is_numeric($status) ) $this->set_error('订单状态不能为空');

        $order_info = $this->my_order->receive_order_info( $order_id , $this->_id ,$status );
        if( $order_info ){
            $this->set_success('ok',$order_info);
        }else{
            $this->set_error('不存在的订单');
        }
    }
    /**
     * 我的任务-取消订单
     */
    public function cancel_order()
    {

        if( !IS_POST ) $this->set_error('我需要你POST提交数据');
        $posts = I('post.');
        if( !$posts['order_id'] ) $this->set_error('你需要提交订单ID');
        //if( !$posts['content'] ) $this->set_error('你需要提交取消原因');
        if( empty($posts['content']) ){
            $posts['content'] = '您已取消该订单';
        }


        $res = $this->my_order->cancel_order( $posts['order_id'] , $this->_id ,$posts['content']);
        if( is_numeric($res) ){
            $this->set_success('取消成功',$res);
        }else{
            $this->set_error($res);
        }
    }
    /**
     * 我的任务-完成任务
     */
    public function seller_complete_order()
    {
        if( !IS_POST ) $this->set_error('我需要你POST提交数据');
        $posts = I('post.');
        if( !$posts['order_id'] ) $this->set_error('你需要提交订单ID');

        $res = $this->my_order->seller_complete_order( $posts['order_id'] , $this->_id );
        if( is_numeric($res) ){
            $this->set_success('操作成功',$res);
        }else{
            $this->set_error($res);
        }
    }
    /**
     * 我的任务-完成任务
     */
    public function member_complete_order()
    {
        if( !IS_POST ) $this->set_error('我需要你POST提交数据');
        $posts = I('post.');
        if( !$posts['order_id'] ) $this->set_error('你需要提交订单ID');

        $res = $this->my_order->member_complete_order( $posts['order_id'] , $this->_id );
        if( is_numeric($res) ){
            $this->set_success('操作成功',$res);
        }else{
            $this->set_error($res);
        }
    }
}