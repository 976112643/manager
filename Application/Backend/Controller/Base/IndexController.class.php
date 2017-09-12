<?php
namespace Backend\Controller\Base;

use Common\Help\HttpHelp;
//use Backend\Controller\Count\IndexController;

/**
 * 后台入口
 */
class IndexController extends AdminController
{
    public $count;
    public $start_time;
    public $end_time;

    /**
     * [__autoload 父类继承]
     * @return [type] [description]
     */
    public  function __autoload(){
        parent::__autoload();
        $this->order_count=A('Backend/Count/Index','Controller','1'); /*统计类*/
    }
    
    /**
     * 订单状态分组
     * @var array
     */
    public $status_code = array(
            '10'=>array('title'=>'待支付','class'=>'alert-error'),
            '20'=>array('title'=>'待接单','class'=>'label-waring'),
            '21'=>array('title'=>'申请退款','class'=>'label-waring'),
            '22'=>array('title'=>'排队中','class'=>'label-waring'),
            '30'=>array('title'=>'任务进行中','class'=>'label-success'),
            '40'=>array('title'=>'待评价','class'=>'label-success'),
            '45'=>array('title'=>'接单人已完成','class'=>'label-success'),
            '50'=>array('title'=>'任务完成','class'=>'alert-error'),
            '90'=>array('title'=>'任务关闭','class'=>'alert-error'),
            '99'=>array('title'=>'已取消','class'=>'alert-error'),
            '100'=>array('title'=>'订单删除','class'=>'alert-error'),
    );

    /**
     * 系统总览统计
     * @author 鲍海
     * @time 2017-03-28
     */
    public function index()
    {
        $now_day_time = date('Y-m-d',time());
        
        /*获取用户注册量*/
        /**
         * 同APP下载一样，优化成一条SQL语句,加索引
         * @var [type]
         */
        $new_member_count = get_result('member_count',array('add_time'=>$now_day_time),'','count,type'); 
        $data = array_column($new_member_count,'count','type');
        $member_count = $data['10'] ? $data['10'] : 0;
        $this->assign('member_count',$member_count);
                
        /*获取今日调用过API接口的用户*/
        $member_call_api_count = count_data('member',array('call_api_time'=>$now_day_time));
        $this->assign('member_call_api_count',$member_call_api_count);
        
        // /*获取今日下单总金额*/
        // $pingtai_count = get_info('pingtai_count',array('add_time'=>$now_day_time));
        // $this->assign('pingtai_total',number_format($pingtai_count['total'], 2, '.', ''));
        // $this->assign('pingtai_remove_total',number_format($pingtai_count['remove_total'], 2, '.', ''));
        
        
        // /*已支付订单*/
        // $data['date_list']=$this->order_count->date_list('month');
        // $this->count_order(json_decode($data['date_list'],true),array());
        /*注册用户量*/
        // $data['date_list']=$this->order_count->date_list('month');
        // $this->count_member(json_decode($data['date_list'],true),array());
        
        /*获取最新订单*/
        // $map = array();
        // $order_detail = get_result(D('OrderShopMemberView'),$map,'id desc','id,order_no,realname,nickname,status,add_time',10);
        // $this->assign('order_detail',$order_detail);
        
        /*获取最新用户*/
        // $map = array();
        // $member_detail = get_result(D('MemberinfoView'),$map,'id desc','id,nickname,mobile,is_hid,register_time',10);
        // $this->assign('member_detail',$member_detail);
        
        // /** 订单状态*/
        // $this->assign('order_status',$this->status_code);
        //dump($data);
        //die;
        $this->assign($data);
        $this->display();
    }

    // /**
     // * 统计订单
     // */
    // protected function count_order($date,$map)
    // {
        // /*已支付订单*/
        // $one = $this->count_data('pingtai_count', 'total', $map, $date,'member','add_time','datetime','user_count');
        // /*未支付订单*/
        // $map['status'] = 10;
        // $two= $this->count_data('order', 'money_total', $map, $date,'new_order');
        // $data['user_list'] = array_overlay($one,$two);
        
        // /*申请退款订单*/
        // /*$map['status'] = 21;
        // $three= $this->count_data('order', 'money_total', $map, $date,'refund_order');
        // $data['user_list'] = array_overlay($three,$data['user_list']);*/
        
        // /*交易关闭订单*/
        // /*$map['status'] = 90;
        // $four= $this->count_data('order', 'money_total', $map, $date,'cancel_order');
        // $data['user_list'] = array_overlay($four,$data['user_list']);*/
        // $this->assign($data);
    // }


    /**
     * 统计人员
     */
    protected function count_member($date)
    {
        // $data = array();
        // /*用户端*/
        // $one = $this->count_data('member_count', 'count', array(), $date,'m_member','add_time','time','m_user_count');
        
        // $data['m_user_list'] = $one;
        // $this->assign($data);
    }

}