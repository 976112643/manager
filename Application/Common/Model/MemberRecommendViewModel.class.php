<?php
namespace Common\Model;
use Think\Model\ViewModel;

    /**
     * 用户推广模型
     * @author  陶君行
     * @time    2016.11.05
     */
    class MemberRecommendViewModel extends ViewModel{
        /**
         * 模型字段定义
         * @var array
         */
        public $viewFields = array(     
            'recommend'=>array(
                '*',
                '_as'=>'a',
                '_type'=>'LEFT'
            ),
            'member_1'=>array(
                'nickname'=>'parent_nickname',
                '_table' =>'sr_member_info',
                '_on'=>'a.pid=member_1.uid',
                '_type'=>'LEFT'
            ),
            'member_2'=>array(
                'nickname' =>'my_nickname',
                '_table' =>'sr_member_info',
                '_on'=>'a.member_id=member_2.uid',
                '_type'=>'LEFT'
            ),
        ); 
    }