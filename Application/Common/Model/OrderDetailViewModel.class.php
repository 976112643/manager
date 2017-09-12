<?php
namespace Common\Model;
use Think\Model\ViewModel;

/**
 * 用户订单列表
 */
class OrderDetailViewModel extends ViewModel{
    public $viewFields = array(
        'order'=>array(
            '*',
            '_as'=>'a',
            '_type' => 'left' ,
        ),
        'member_info'=>array(
            'nickname' =>'seller_nickname',
            'mobile' =>'seller_mobile',
            'head_img' =>'seller_head_img',
            'title_id' =>'seller_title_id',
            'start_rating' =>'seller_start_rating',
            '_type' => 'left' ,
            '_as'=>'d',
            '_on'=>'a.seller_id=d.uid',
        ),
        'member2' =>array(
            'nickname' =>'member_nickname',
            'mobile' =>'member_mobile',
            'head_img' =>'member_head_img',
            'title_id' =>'member_title_id',
            'start_rating' =>'member_start_rating',
            '_table' =>'sr_member_info',
            '_type' =>'left',
            '_as' =>'b',
            '_on' =>'a.member_id = b.uid'
        )
    ); 
}