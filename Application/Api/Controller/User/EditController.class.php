<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/9
 * Time: 20:56
 */
namespace Api\Controller\User;

use Common\Help\DateHelp;

class EditController extends IndexController
{
    protected function __init()
    {
        parent::__init();
        if( !IS_POST )   $this->set_error('想要修改资料，你要POST方式提交数据啊！');
    }

    /**
     * 修改头像
     */
    public function edit_headimg()
    {
        $info = $this->person->edit_head_img( $this->_id );
        if( is_numeric($info['res']) ){
            $this->set_success('更新成功!',array('head_img'=>$info['img']));
        }else{
            $this->set_error($info);
        }
    }
    /**
     * 添加我的相册
     */
    public function add_photo()
    {
        $res = $this->personPhoto->add_photo( $this->_id );
        if(is_numeric($res)) {
            $this->set_success('更新成功！');
        }
        $this->set_error($res);
    }
    /**
     * 删除我的相册中的照片
     */
    public function del_photo()
    {
        $ids = explode(',',I('ids'));
        if( count($ids) < 1 ) $this->set_error('请给需要删除的图片ID啊'); 
        $res = $this->personPhoto->del_photo( $this->_id,$ids);
        if(is_numeric($res)) {
            $this->set_success('删除成功！');
        }
        $this->set_error($res);
    }
    /**
     * 修改用户名
     */
    public function edit_nickname()
    {
        $this->check_post_field('nick_name');
        $res = $this->person->edit_key_value( $this->_id,'nickname',I('nick_name') );
        if(is_numeric($res)) {
            $this->set_success('更新成功！', I('nick_name'));
        }
        $this->set_error($res);
    }

    /**
     * 修改年龄
     */
    public function edit_age()
    {
        /** @var  根据生日计算年龄 */
        $date = new DateHelp();

        $age = $date->calc_age(I('age'));
        $res = $this->person->edit_key_value( $this->_id,'age',$age );
        if(is_numeric($res)) {
            $constellation = $date->get_constellation(I('age'));
            $this->person->update_redis_value($this->_id,'constellation',$constellation);
            $this->set_success('更新成功！', $age);
        }
        $this->set_error($res);
    }
    /**
     * 修改性别
     */
    public function edit_gender()
    {
        $this->check_post_field('gender');
        $gender = I('gender');
        $res = $this->person->edit_key_value( $this->_id,'gender',$gender );

        if(is_numeric($res)) {
            /** 修改Redis的内容 */
            $sex = array('保密','男','女');
            $gender = $sex[$gender];
            $this->person->update_redis_value($this->_id,'gender_text',$gender);
            $this->set_success('更新成功！', $gender);
        }
        $this->set_error($res);
    }
    /**
     * 修改个性签名
     */
    public function edit_signature()
    {
        $this->check_post_field('signature');
        $res = $this->person->edit_key_value( $this->_id,'signature',I('signature') );
        if(is_numeric($res)) {
            $this->set_success('更新成功！', I('signature'));
        }
        $this->set_error($res);
    }
    /**
     * 修改我的技能
     */
    public function edit_skill()
    {
        $this->check_post_field('skill');
        $res = $this->person->edit_key_value( $this->_id,'skill',I('skill') );
        if(is_numeric($res)) {
            $this->set_success('更新成功！', I('skill'));
        }
        $this->set_error($res);
    }
    /**
     * 修改我的爱好
     */
    public function edit_hobbies()
    {
        $this->check_post_field('hobbies');
        $res = $this->person->edit_key_value( $this->_id,'hobbies',I('hobbies') );
        if(is_numeric($res)) {
            $this->set_success('更新成功！', I('hobbies'));
        }
        $this->set_error($res);
    }
    /**
     * 修改我当前的头衔
     */
    public function edit_title()
    {
        $title_id = I('title_id');
        $this->check_post_field('title_id');
        $res = $this->title->edit_title_id( $this->_id,'title_id',$title_id );
        if(is_numeric($res)) {
            $this->set_success('更新成功！', $title_id);
        }
        $this->set_error($res);
    }
    /**
     * 修改密码-获取手机号
     */
    public function edit_pay_password_one()
    {
        /** 获取原先绑定的手机号,然后发送验证码 */
        $info = $this->person->person_info($this->_id);
        /**
         * 如果是get方式提交，就给出手机号
         */
        $mobile = array('mobile' => substr_replace($info['mobile'], '****', 3, 4));
        $this->set_success('ok',$mobile);      
    }
    /**
     * 修改密码-发送手机号
     */
    public function edit_pay_password_by_send_code()
    {

        $info = $this->person->person_info($this->_id);
        $mobile = $info['mobile'];
        
        if( !preg_match(MOBILE,$mobile) ) $this->set_error('手机号格式不正确');

        $content = $this->send_mobile_code($mobile,4);

        $this->set_success($content);
    }
    /**
     * 修改密码-检查手机号验证码
     */
    public function edit_pay_password_by_check_code()
    {

        $info = $this->person->person_info($this->_id);
        $mobile = $info['mobile'];
        
        if( !preg_match(MOBILE,$mobile) ) $this->set_error('手机号格式不正确');

        $has = $this->verify_mobile_code($mobile,I('code'));

        $this->set_success('验证成功');
    }
    /**
     * 修改支付密码
     */
    public function edit_pay_password()
    {
        $res = $this->person->edit_pay_password($this->_id);
        if(is_numeric($res)) {
            $this->set_success('更新成功！', $title_id);
        }
        $this->set_error($res);
    }
    /**
     * 绑定手机号-手机短信换绑-1
     */
    public function bind_phone_by_code_one()
    {
        $this->edit_pay_password_one();
    }
    /**
     * 绑定手机号-发送旧手机验证码
     */
    public function send_bind_mobile_code()
    {
        if( !IS_POST ) $this->set_error('请注意请求方式');
        $mobile = I('post.mobile');
        
        if( !$mobile ){
            $info = $this->person->person_info($this->_id);
            $mobile = $info['mobile'];
        }
        if( !preg_match(MOBILE,$mobile) ) $this->set_error('手机号格式不正确');

        $content = $this->send_mobile_code($mobile,4);

        $this->set_success($content);
    }
    /**
     * 绑定手机号-验证旧手机验证码
     * @return [type] [description]
     */
    public function bind_phone_by_code_two()
    {
        $this->edit_pay_password_by_check_code();
    }
    /**
     * 绑定手机号
     */
    public function bind_phone(){
        $mobile = I('mobile');
        /** 验证手机号是否合适 */
        if($mobile == ''){
            $this->set_error('请输入手机号');
        }
        if(!preg_match(MOBILE,$mobile))     $this->set_error('手机号格式不正确');
        
        $has = $this->verify_mobile_code($mobile,I('code'));
        $map = array(
            'mobile' =>$mobile,
            'id'    =>array('neq',$this->_id)
        );  
        $member_info = get_info('member',$map,'id');
        if($member_info['id']){
            $this->set_error('您输入的手机号已经被其他用户绑定，请更换手机号');
        }
        /** 修改手机号 */
        $result = $this->person->edit_key_value($this->_id,'mobile',$mobile);
        
        if(is_numeric($result)){
            $this->set_success('绑定成功');
        }else{
            $this->set_error($result);
        }
        
    }
    /**
     * 验证POST字段是否存在
     */
    public function check_post_field($field)
    {
        $post = I('post.');
        if( !isset($post[$field] ) ){
            $this->set_error($field.'你得传给我啊！');
        }
    }
    /**
     * 第三方首次登录-绑定手机号
     */
    public function extends_login_bind_phone()
    {
        $mobile = I('mobile');
        /** 验证手机号是否合适 */
        if($mobile == ''){
            $this->set_error('请输入手机号');
        }
        if(!preg_match(MOBILE,$mobile))     $this->set_error('手机号格式不正确');
        
        $has = $this->verify_mobile_code($mobile,I('code'));
        /** 绑定账号 */
        $res = $this->person->login_bind_mobile($mobile,$this->_id);

        if( !is_numeric($res['res']) ){
            $this->set_error($res);
        }
        $member_info = $res['user_info'];

        $user_info['user_key'] = $this->get_user_key($member_info['id'],$member_info['login_time'],1);
        $user_info['user_id'] = $member_info['id'];
        $user_info['user_mobile'] = $member_info['mobile'];
        $this->set_success('绑定成功',$user_info);
    }
    /**
     * 设置我的推广人员
     */
    public function set_user_recommend()
    {
        $this->check_post_field('recommend_code');
        $result = $this->person->add_recommend(I('recommend_code','','trim'),$this->_id);
        if(is_numeric($result)){
            $this->set_success('设置成功');
        }else{
            $this->set_error($result);
        }
    }
}