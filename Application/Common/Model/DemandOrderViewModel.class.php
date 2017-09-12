<?php
namespace Common\Model;
use Think\Model\ViewModel;

    /**
     * 订单表与用户表
     * @author  鲍海
     * @time    2017.3.29
     */
    class DemandOrderViewModel extends ViewModel{
        public $viewFields = array(     
            'order'=>array(
                '*',
                '_as'=>'a',
                '_type'=>'LEFT'
            ),
            'member_info'=>array(
            	'nickname'=>'member_nickname',
                'mobile',
                'head_img',
                'start_rating',
                'title_id',
            	'_as'=>'b',
                '_on'=>'a.member_id=b.uid',
            ),
            'member_title' =>array(
                'name',
                'type',
                '_as'=>'c',
                '_on'=>'b.title_id=c.id',
            )
        ); 
    }