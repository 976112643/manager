<?php
namespace Common\Model;
use Think\Model\ViewModel;

    /**
     * 用户地址模型
     * @author  鲍海
     * @time    2017.04.06
     */
    class MemberAddressViewModel extends ViewModel{
        /**
         * 模型字段定义
         * @var array
         */
        public $viewFields = array(     
            'member'=>array(
                '*',
                '_as'=>'a',
                '_type'=>'LEFT'
            ),
            'member_address'=>array(
                'count(a.id)'=> 'sum',
                'is_default',
                'province',
                'city',
                'area',
                '_on'=>'a.id=member_address.member_id',
            )
        ); 
    }