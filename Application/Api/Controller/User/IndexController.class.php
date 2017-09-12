<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/9
 * Time: 20:46
 */
namespace Api\Controller\User;

use Api\Controller\Base\AuthController;
use Common\Plugin\Person;
use Common\Plugin\PersonPhoto;
use Common\Plugin\Title;
/**
 * 用户信息主类
 * @package Api\Controller\User
 */
class IndexController extends AuthController
{
    /** @var  个人对象 */
    protected $person;
    protected $personPhoto;
    protected $title;
    protected function __init()
    {
        $this->person = new Person();
        $this->personPhoto = new PersonPhoto();
        $this->title = new Title();
    }
    /**
     * 获取用户信息
     */
    public function get_user_info()
    {
        $info = $this->person->person_info($this->_id);
        if( $info['uid'] ){
            unset($info['sum_task_num']);
            unset($info['sum_money_num']);
            $this->set_success('ok',$info);
        }else{
            $this->set_error('获取用户信息失败');
        }
    }
    /**
     * 获取我的相册
     */
    public function get_user_photo()
    {
        $res = $this->personPhoto->my_photo($this->_id);
        if( $res ){
            $this->set_success('ok',array('photo_list'=>$res));
        }else{
            $this->set_error('暂无数据');
        }
        
    }
    /**
     * 获取我的头衔
     */
    public function get_user_title()
    {
        $data = $this->title->get_user_title( $this->_id );
        $this->set_success('ok',array('list'=>$data));
    }
    /**
     * 获取我的余额
     */
    public function get_user_balance()
    {
        $map = array(
            'id' =>$this->_id
        );
        $info = get_info('member',$map,'balance');
        $this->set_success('ok',$info);
    }
    /**
     * 获取我的推广人数
     */
    public function get_user_recommend_num()
    {
        $map = array(
            'id' =>$this->_id
        );
        $info = get_info('member',$map,'id,level_one_num,level_two_num,level_three_num,recommend_code');
        $sum = array_sum( array($info['level_one_num'],$info['level_two_num'],$info['level_three_num']) );
        $count = count_data('recommend',array('member_id'=>$this->_id));
        $return_data = array(
            'recommend_num' =>$sum,
            'recommend_code' =>$info['recommend_code'],
            'is_recommend' => $count ? '1' : '0',
        );
        $this->set_success('ok',$return_data);
    }
}