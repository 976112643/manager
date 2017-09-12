<?php
namespace Common\Model;
use Think\Model\ViewModel;

    /**
     * 资质认证和用户模型
     * @author  鲍海
     * @time    2017.03.16
     */
    class MemberCertificationViewModel extends ViewModel{
        /**
         * 模型字段定义
         * @var array
         */
        public $viewFields = array(     
            'member_certification'=>array(
                '*',
                '_as'=>'a',
                '_type'=>'LEFT'
            ),
            'member'=>array(
                'mobile'=>'member_mobile',
                'nickname'=>'member_nickname',
                '_as'=>'b',
                '_type'=>'LEFT',
                '_on'=>'a.member_id=b.id'
            ),
            'member_info'=>array(
                'realname'=>'realname',
                '_as'=>'c',
                '_on'=>'a.member_id=c.member_id'
            ),
        ); 
    }