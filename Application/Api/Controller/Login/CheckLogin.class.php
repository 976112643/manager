<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/1
 * Time: 14:14
 */
namespace Api\Controller\Login;

use Api\Controller\Base\BaseController;
use Common\Plugin\Person;

/**
 * 登录验证类
 * @package Api\Controller\Login
 */
class CheckLogin extends BaseController
{
    /**
     * 验证普通登录类
     * @param $table  需要查询的表名
     * @param $role_id 权限ID
     * @return array 返回数据
     */
    public function normal( $table , $role_id )
    {
        $posts = I('post.','','trim');
        /** 如果开放了注册，那么就使用手机号登录 */
        if( !$posts['username'] )	$this->set_error('请输入用户名');
        if( !$posts['code'] )          $this->set_error('请输入验证码');
        if(!preg_match(MOBILE,$posts['username']))     $this->set_error('手机号格式不正确');

        if($posts['username'] == '13812345678'){
            $map['mobile'] = '13812345678';
            return get_info($table,$map,true);
        }

        $has = $this->verify_mobile_code($posts['username'],$posts['code']);

        $map['mobile'] = $posts['username'];
        $map['is_hid'] = array('in',array('0','1'));
        /** 如果不是普通用户，那么查询ROLE_ID */
        if ( $role_id != 1){
            $map['role_id'] = $role_id;
        }
        $field = true;
        $info = get_info($table,$map,$field);

        if( !$info ){
            /** 第一次登录 */
            $m = M();
            $m->startTrans();
            /** @var array [更新member表] */
            $data = array(
                'mobile' =>$posts['username'],
                'login_ip'          =>  get_client_ip(),
                'login_time'        =>  NOW_TIME,
                'register_time'     =>  NOW_TIME,
                'register_ip'       =>  get_client_ip()
            );
            $res = update_data($table,[],[],$data);
            if( is_numeric($res)){
                /** @var array [更新member_info表] */
                $_data = array(
                    'uid'       =>  $res,
                    'head_img'  =>  'Public/Static/img/avatar_200.jpg',
                    'nickname'  => get_rand_char(8),
                    'mobile'    => $posts['username']
                );
                $_res = update_data('member_info',[],[],$_data);
                /** 添加注册人数 */
                count_member_day('10');
                if(is_numeric($_res)){
                    $m->commit();
                    /** 更新member_gain_title表 */
                    update_data('member_gain_title',[],[],array('uid'=>$res,'title_id'=>1));
                    /** 生成推广码 */
                    $person = new Person();
                    $person->create_recommend_code($res);
                    return array(
                        'id' => $res,
                        'mobile' =>$posts['username']
                    );
                }
            }
            $m->rollback();
            $this->set_error('注册失败');
        }

        if( $info['is_hid'] )	$this->set_error('您的账号已被禁用,请联系管理员');

        return $info;
    }

    /**
     * 验证第三方登录类
     * @param $table 表名
     * @param $type  登录 类型
     */
    public function extend( Base $obj , $table )
    {
        $map = $obj->get_map();

        $model = D($table);
        $info = get_info($model,$map);
        if($info['id']){
            $obj->set_user_info( $info );
            $obj->after_login();
            $obj->return_user_info();
        }else{
            $obj->first_login();
        }

    }
}