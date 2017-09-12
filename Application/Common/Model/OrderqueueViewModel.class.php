<?php
namespace Common\Model;
use Think\Model\ViewModel;

    /**
     * 订单与订单排队表
     * @author  鲍海
     * @time    2017.2.20
     */
    class OrderqueueViewModel extends ViewModel{
        public $viewFields = array(     
            'order_queue'=>array(
                'uid',
                'add_time',
                '_as'=>'a',
                '_type'=>'LEFT'
            ),
            'member_info'=>array(
            	'nickname'=>'seller_nickname',
                'mobile' =>'seller_mobile',
            	'head_img'=>'seller_head_img',
                'start_rating',
                '_on'=>'a.uid=member_info.uid',
            	'_type'=>'LEFT',
            )
        ); 
    }