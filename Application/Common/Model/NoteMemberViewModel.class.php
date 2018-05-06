<?php
namespace Common\Model;
use Think\Model\ViewModel;

    /**
     * 笔记带用户模型
     */
    class NoteMemberViewModel extends ViewModel{
        /**
         * 模型字段定义
         * @var array
         */
        public $viewFields = array(     
            'note'=>array(
                '*',
                '_as'=>'a',
                '_type'=>'LEFT'
            ),
            'member_info'=>array(
                'nickname',
                'head_img',
                'device',
                'device_brand',
                'device_model',
                'device_man',
                '_on'=>'a.uid=member_info.uid',
            )
        ); 
    }