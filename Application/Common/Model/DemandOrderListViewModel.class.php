<?php
namespace Common\Model;
use Think\Model\ViewModel;

    /**
     * 需求大厅与订单表
     * @author  鲍海
     * @time    2017.3.29
     */
    class DemandOrderListViewModel extends ViewModel{
        public $viewFields = array(     
            'demand'=>array(
                '*',
                '_as'=>'a',
                '_type'=>'LEFT'
            ),
            'order'=>array(
            	'order_no',
            	'_as'=>'b',
                '_on'=>'a.order_id=b.id',
            )
        ); 
    }