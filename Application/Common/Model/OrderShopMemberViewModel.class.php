<?php
namespace Common\Model;
use Think\Model\ViewModel;

    /**
     * 订单与门店/用户模型
     * @author  鲍海
     * @time    2017.02.20
     */
    class OrderShopMemberViewModel extends ViewModel{
        public $viewFields = array(     
            'order'=>array(
                '*',
                '_as'=>'a',
                '_type'=>'LEFT'
            ),
            /*'shop'=>array(
                'contact_people'=>'contact_people',
                'contact_tel'=>'contact_tel',
                'title'=>'shop_title',
                'logo'=>'shop_logo',
                '_on'=>'a.shop_id=shop.id',
            ),*/
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
                '_as'=>'c',
                '_table' => 'sr_member_info',
                '_on'=>'a.seller_id=c.uid',
            ),
        ); 
    }