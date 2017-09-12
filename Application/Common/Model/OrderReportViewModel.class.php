<?php
namespace Common\Model;
use Think\Model\ViewModel;

    /**
     * 订单与举报表
     * @author  鲍海
     * @time    2017.2.20
     */
    class OrderReportViewModel extends ViewModel{
        public $viewFields = array(     
            'order_report'=>array(
                'id',
                'order_id',
                'uid',
                'add_time',
                '_as'=>'a',
                '_type'=>'LEFT',
            ),
            'order_1'=>array(
                'order_no',
                'detail_title',
                'description' ,
                'money_total',
                'member_id',
                '_table' =>'sr_order',
                '_on'=>'a.order_id=order_1.id',
                '_type'=>'LEFT',
            ),
            'member_2'=>array(
            	'nickname'=>'nickname',
                'mobile' =>'mobile',
                '_table'=>'sr_member_info',
                '_on'=>'a.uid=member_2.uid',
            	'_type'=>'LEFT',
            ),
            'member_1'=>array(
                'nickname' =>'member_nickname',
                '_table' =>'sr_member_info',
                '_as' =>'c',
                '_on' =>'order_1.member_id = c.uid',
                '_type'=>'LEFT',
            ),
        ); 
    }