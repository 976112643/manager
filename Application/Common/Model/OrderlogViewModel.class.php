<?php
namespace Common\Model;
use Think\Model\ViewModel;

    /**
     * 订单日志与用户模型
     * @author  鲍海
     * @time    2017.2.20
     */
    class OrderlogViewModel extends ViewModel{
        public $viewFields = array(     
            'order_log'=>array(
                '*',
                '_as'=>'a',
                '_type'=>'LEFT'
            ),
            'member_info'=>array(
            	'nickname'=>'member_nickname',
                'mobile',
                '_on'=>'a.member_id=member_info.uid',
            	'_type'=>'LEFT',
            ),
            'admin'=>array(
                'nickname'=>'admin_nickname',
                '_on'=>'a.admin_id=admin.id',
            )
        ); 
    }