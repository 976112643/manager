<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/1
 * Time: 13:52
 */
namespace Api\Controller\Login;

use Common\Plugin\Person;

/**
 * 普通用户登录类
 * @package Api\Controller\Login
 * @auther taojunxing
 */
class MemberLogin extends Base
{
    protected $user_info = array();

    protected $info_table = 'member_info';
    /**
     * 构造方法
     */
    protected function __init()
    {
        parent::__init();
        $this->table = 'member';
    }
    /**
     * 登录
     */
    public function login()
    {
        /** 验证是否可以登录 */
        $check = new CheckLogin();
        $this->user_info = $check->normal( $this->table , 1);
        /** 执行登录后的更新操作 */
        $this->after_login();
        /** 返回登录成功后的信息 */
        $this->return_user_info();
    }
    /**
     * 设置人员数组
     * @param $info
     */
    public function set_user_info( $info )
    {
        $this->user_info = $info;
    }
    /**
     * 执行登录后的更新操作
     */
    public function after_login()
    {
        try{
            //更新登陆时间
            $data = array(
                'id'  => $this->user_info['id'],
                'login_time' =>time(),
                'login_ip' =>get_client_ip()
            );
            update_data($this->table,[],[],$data);
            //插入到用户缓存中
            $person = new Person();
            $person->add_info_to_redis( $this->user_info['id'] );
            /** 更新登录时间，获取用户秘钥 */
            $person->update_redis_value($data['id'],'login_time',$data['login_time']);
            $this->user_info['user_key'] = $this->get_user_key($data['id'],$data['login_time'],1);
        }catch(\Exception $e){
            $this->set_error($e->getMessage());
            write_debug(M()->_sql());
        }
    }
    /**
     * 返回登录成功后的信息
     */
    public function return_user_info()
    {
        $user_info = array();
        $info = $this->user_info;
        $user_info['user_key'] = $info['user_key'];
        $user_info['user_id'] = $info['id'];
        $user_info['user_mobile'] = $info['mobile'];
        $this->set_success('登录成功',$user_info);
    }
    /**
     * 第三方首次登录注册
     * @return [type] [description]
     */
    public function first_login()
    {
        $posts = I('post.');
        $model = M();
        $model->startTrans();
        /** 更新member表 */
        $data = array(
            'qq_open_id' 		=>	$posts['qq_open_id'],
            'weixin_open_id' 	=>	$posts['wx_open_id'],
            'sina_open_id'		=>	$posts['sina_open_id'],
            'login_ip'			=>	get_client_ip(),
            'login_time'		=>	time(),
            'register_time'		=>	time(),
            'register_ip'		=>	get_client_ip(),
            'is_extract'		=>	1
        );
        $res = update_data($this->table,[],[],$data);
        if( is_numeric($res) ){
            /** 更新member_info表 */
            $rules = array(
                array('nickname','require','请确认用户名',1),
                array('nickname','1,20','用户名不得超过20位',1,'length'),
                array('head_img','require','头像必须',1),
            );
            /** 检查用户昵称是否重复，如果重复就加后缀 */
            $map = array(
                'nickname' =>array(
                    'like','%'.$posts['nickname'].'%'
                )
            );
            $count = count_data($this->info_table,$map);

            $_data = array(
                'uid' 		=>	$res,
                'head_img'  =>	$posts['head_img'],
            );
            $_data['nickname'] = $count ?  $posts['nickname'] .$count : $posts['nickname'];


            $_res = update_data($this->info_table,$rules,[],$_data);
            /** 更新member_gain_title表 */
            update_data('member_gain_title',[],[],array('uid'=>$res,'title_id'=>1));

            /** 添加注册人数 */
            count_member_day('10');
            if( is_numeric($_res) ){
                $model->commit();
                /** 生成推广码 */
                $person = new Person();
                $person->create_recommend_code($res);
                /** 返回用户信息 */
                $info = array(
                    'id' =>$res,
                    'mobile' =>''
                );
                $this->user_info = $info;
                $this->after_login();
                $this->return_user_info();
            }else{
                $model->rollback();
                $this->set_error($_res);
            }
        }
        $model->rollback();
        $this->set_error('获取用户信息失败');
    }
}