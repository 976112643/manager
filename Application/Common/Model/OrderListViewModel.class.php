<?php
namespace Common\Model;
use Think\Model\ViewModel;

/**
 * 用户订单列表
 */
class OrderListViewModel extends ViewModel{
    public $viewFields = array(
        'order'=>array(
            '*',
            '_as'=>'a',
            '_type' => 'left' ,
        ),
        'member'=>array(
            'nickname',
            'mobile',
            'head_img',
            'star_rating_service',
            'star_rating_profession',
            'star_rating_environment',
            'certification_type',
            '_type' => 'left' ,
            '_as'=>'d',
            '_on'=>'a.seller_id=d.id',
        ),
        'member_info'=>array(
            'realname',
            '_as'=>'e',
            '_on'=>'a.seller_id=e.member_id',
        ),
        /*'member_certification'=>array(
            'type'=>'member_certification_type',
            'status'=>'member_certification_status',
            '_as'=>'e',
            '_on'=>'a.seller_id=e.member_id',
        ),*/
    ); 
}