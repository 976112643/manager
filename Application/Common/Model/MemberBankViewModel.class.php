<?php
namespace Common\Model;
use Think\Model\ViewModel;

    /**
     * 用户银行卡模型
     * @author  胡尧
     * @time    2016.11.05
     */
    class MemberBankViewModel extends ViewModel{
        /**
         * 模型字段定义
         * @var array
         */
        public $viewFields = array(     
            'member_bank'=>array(
                '*',
                '_as'=>'a',
                '_type'=>'LEFT'
            ),
            'bank'=>array(
                'name',
                'img',
                '_on'=>'a.bank_id=bank.id',
            )
        ); 
    }