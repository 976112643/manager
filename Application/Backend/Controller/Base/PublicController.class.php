<?php
namespace Backend\Controller\Base;

use Common\Controller\CommonController;

/**
 * 后台登录
 * 
 * @author 秦晓武
 *         @time 2016-05-31
 */
class PublicController extends CommonController
{

    /**
     * 表名
     * 
     * @var string
     */
    public $table = 'admin';

    /**
     * 登录
     * 
     * @author 秦晓武
     *         @time 2016-05-31
     */
    public function login()
    {
	
        if (! IS_POST) {
            header("location:" . __ROOT__ . "/Backend");
        }
		
        $username = I('username');
        $password = I('password');
        if ($username == '') {
            $this->error('请输入用户名');
        }
        if ($password == '') {
           // $this->error('请输入密码');
        }
        if ($this->check_verify(I('code')) != 1) {
            //$this->error('验证码不正确');
        }
        $info = get_info('admin', array(
            'username' => $username
        ));
        if (! $info['id'] || ($info['password'] != md5(md5($password) . $info['salt']))) {
            //$this->error('用户名或密码错误');
        }
        if ($info['is_hid'] !== '0') {
            $this->error('账户已禁用');
        }
        $member_id = $info['id'];
        /**
         * 更新登录时间和登录IP
         */
        $data['id'] = $member_id;
        $data['login_time'] = date('Y-m-d H:i:s');
        $data['login_ip'] = get_client_ip();
        $data['last_login_time'] = $info['login_time'];
        $data['last_login_ip'] = $info['login_ip'];
        update_data('admin', array(), array(), $data);
        if ($info['role_id']) {
            $info_role = get_info('admin_role', array(
                'id' => $info['role_id']
            ));
            $info['title'] = $info_role['title'];
        } else {
            $info['title'] = '超级管理员';
            $info_role = array();
        }
        session('username', $username);
        session('member_id', $member_id);
        session("member_info", $info);
        session("member_role", $info_role);
        action_log();
		
        // get_menu_List();
        $this->success('登录成功', __ROOT__ . "/Backend");
    }

    /**
     * 退出登录
     * @time 2014-12-25
     * 
     * @author 郭文龙 <2824682114@qq.com>
     */
    function logout()
    {
        session(null); // 清空当前的session
        $this->success('退出成功', U('backend/base/public/login'));
    }
}
