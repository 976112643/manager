<?php
namespace Common\Model;
use Think\Model\ViewModel; 

    /**
     * 评论与用户模型
     * @author  胡尧
     * @time    2016.11.24
     */
    class ServiceCommentViewModel extends ViewModel{
        /**
         * 评论模型字段定义
         * @var array
         */
        public $viewFields = array(     
            'order_comment'=>array(
                '*',
                '_as'=>'a',
                '_type'=>'LEFT'
            ),
            'member_1'=>array(
                'mobile'=>'mobile',
                'nickname'=>'nickname',
                'head_img'=>'m_head_img',
                '_as'=>'b',
                '_table' => 'sr_member_info',
                '_on'=>'a.member_id=b.uid',
                '_type'=>'LEFT'
            ),
            'member_2'=>array(
                'mobile'=>'seller_mobile',
                'nickname'=>'seller_nickname',
                'head_img' =>'s_head_img',
                '_as'=>'c',
                '_table' => 'sr_member_info',
                '_on'=>'a.seller_id=c.uid',
                '_type'=>'LEFT'
            ),
            'order'=>array(
                'order_no',
                'detail_title',
                '_as'=>'d',
                '_on'=>'a.order_id=d.id',
                '_type'=>'LEFT'
            ),
            /*'shop'=>array(
                'province',
                'city',
                'telephone',
                'title'=>'shop_title',
                '_as'=>'c',
                '_on'=>'a.shop_id=c.id',
            ),*/
            /*'shopper'=>array(
                'cover'=>'shopper_cover',
                'work_number',
                'title'=>'shopper_title',
                'realname'=>'shopper_realname',
                '_as'=>'d',
                '_on'=>'a.shopper_id=d.id',
            ),*/
        ); 
    }