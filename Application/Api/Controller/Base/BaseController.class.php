<?php
namespace Api\Controller\Base;

use Common\Controller\ApiController;
use Common\Help\RedisHelp;
use Common\Plugin\Person;

/**
 * API接口基础类
 * @author Administrator
 *
 */
class BaseController extends ApiController
{
    /** 用户ID*/
    protected $_id;

    /**
     * API接口基础类-基础构造函数
     */
    protected function __autoload()
    {
        $this->__init();
        $this->_host = 'http://' . $_SERVER['HTTP_HOST'] . __ROOT__ . '/';
    }

    /**
     * API接口基础类-子类集成构造函数
     */
    protected function __init()
    {
    }

    /**
     * 设置返回成功的信息
     * @time 2016-11-11
     * @author 陶君行<Silentlytao@outlook.com>
     * @param string $msg
     * @param array $info
     * @param string $type
     */
    public function set_success($msg = '', $info = array(), $type = 'json')
    {
        $this->apiReturn(array(
            'status' => '1',
            'msg' => $msg,
            'info' => $info
        ), $type);
    }

    /**
     * 设置返回失败的信息
     * @time 2016-11-11
     * @author 陶君行<Silentlytao@outlook.com>
     * @param string $msg
     * @param array $info
     * @param string $type
     */
    public function set_error($msg = '', $info = '', $type = 'json')
    {
        $this->apiReturn(array(
            'status' => '0',
            'msg' => $msg,
            'info' => $info
        ), $type);
    }

    /**
     * 获取用户的key
     * @param  [type] $member_id  [description]
     * @param  [type] $login_time [description]
     * @return [type]             [description]
     */
    public function get_user_key($member_id, $login_time)
    {
        return think_encrypt($login_time . '_' . $member_id, 'shunshou', 0);
    }

    /**
     * 获取验证的key
     * @param  [type] $key [description]
     * @return [type]      [description]
     */
    public function get_decrypt_key($key)
    {
        return think_decrypt($key, 'shunshou');
    }

    /**
     * 图片上传
     * @author 鲍海
     * @time 2017-03-15
     *   $config = array(
     *       'maxSize'    => 3145728,
     *       'rootPath'   => './',
     *       'savePath'   => 'Uploads/Headimg/',
     *       'saveName'   => array('uniqid',''),
     *       'exts'       => array('jpg', 'gif', 'png', 'jpeg'),
     *       'autoSub'    => true,
     *       'subName'    => array('date','Ymd'),
     *   );
     *  $file 表单name
     * @param  [array] $config [配置]
     * @param  string $file [文件]
     * @return [type]         [description]
     */
    public function upload_files($config, $file = '')
    {
        /* 实例化上传类 */
        $upload = new \Think\Upload($config);
        if (!is_array($_FILES[$file]['name'])) {
            $info = $upload->uploadOne($_FILES[$file]);
            unset($_FILES[$file]);
        } else {
            $info = $upload->upload();
        }
        if (!$info) {
            /* Common::open_file($upload->getError()); */
            return false;
        } else {
            return $info;
        }
    }

    /**
     * 发送创瑞短信
     * @author 鲍海
     * @time 2017-04-11
     */
    public function _send_mobild_code($mobile, $verify)
    {

        $argv = array(
            'name' => C('CR_ACCOUNT'),     //必填参数。用户账号
            'pwd' => C('CR_PWD'),     //必填参数。（web平台：基本资料中的接口密码）
            'content' => '短信验证码为：' . $verify . '，请勿将验证码提供给他人。',   //必填参数。发送内容（1-500 个汉字）UTF-8编码
            'mobile' => $mobile,   //必填参数。手机号码。多个以英文逗号隔开
            'stime' => '',   //可选参数。发送时间，填写时已填写的时间发送，不填时为当前时间发送
            'sign' => C('CR_SIGN'),    //必填参数。用户签名。
            'type' => 'pt',  //必填参数。固定值 pt
            'extno' => ''    //可选参数，扩展码，用户定义扩展码，只能为数字
        );

        foreach ($argv as $key => $value) {
            if ($flag != 0) {
                $params .= "&";
                $flag = 1;
            }
            $params .= $key . "=";
            $params .= urlencode($value);// urlencode($value);
            $flag = 1;
        }
        $url = "http://web.cr6868.com/asmx/smsservice.aspx?" . $params; //提交的url地址
        $con = substr(file_get_contents($url), 0, 1);  //获取信息发送后的状态
        return $con;
    }

    /**
     * 发送验证码
     * @param [type] $[mobile] [手机号]
     * @param [type] $[type] [发送类型]
     * @author 鲍海
     * @time 2017-03-15
     */
    public function send_mobile_code($mobile, $type = 2)
    {
        $msg_info = get_info('member_code', ['mobile_key' => $mobile, 'type' => $type], '', 'send_time desc');
        $now = time();
        if ($now - $msg_info['send_time'] < 30) {
            $this->set_error('请不要频繁发送验证码');
        }

        $code = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
        $content = "您的校验码是：" . $code . "。请不要把校验码泄露给其他人。如非本人操作，可不用理会！";

        $data = array();
        $data['mobile_code'] = $code;
        $data['mobile_key'] = $mobile;
        $data['status'] = 0;
        $data['time'] = $now + 300;
        $data['type'] = $type;
        $data['send_time'] = $now;
        //$data['next_send_time'] = $now+60;
        $result = update_data('member_code', $rules, [], $data);

        $con = send_sms($mobile, $code, 'SMS_71305002', '1');
        //$con = 1;
        if ($con) {
            return '验证短信已发送，请注意查收';
        } else {
            $this->set_error('验证短信发送失败，请联系客服');
        }
    }

    /**
     * 验证验证码
     * @param [type] $[mobile] [手机号]
     * @param [type] $[code] [验证码]
     * @author 鲍海
     * @time 2017-03-15
     */
    public function verify_mobile_code($mobile, $code)
    {
        $code_info = get_info('member_code', array('mobile_code' => $code, 'status' => 0), true, 'id desc');
        if ($code_info['id']) {
            if ($mobile != $code_info['mobile_key']) {
                $this->set_error('请重新获取验证码');
            }
            /*修改验证码状态*/
            $code_info['status'] = 1;
            $res = update_data('member_code', [], [], $code_info);
            return true;
        } else {
            $this->set_error('验证码不正确');
        }
    }

    /**
     * 验证用户信息
     * @param   $[member_id] [用户ID]
     * @author  鲍海
     * @time    2017.03.16
     */
    public function check_member($member_id)
    {
        if (empty($member_id)) $this->set_error('非法操作！');
        $has = get_info('member', array('is_del' => 0, 'id' => $member_id));
        if (!$has) $this->set_error('用户信息不存在');
        if ($has['is_hid']) $this->set_error('账号被禁用');
        return $has;
    }

    /**
     * 防止恶意提交数据
     * 使用Redis阻止
     */
    protected function check_add($member_id, $cache)
    {
        $redis = RedisHelp::getInstance();

        $value = $redis->get($cache);
        /** 验证是否已经提交过了 */
        if ($value) return true;
        /** 如果没有提交，那么插入Redis里面，设置过期时间为5秒 */
        $value = $redis->set($cache, $member_id, 5);
        return $value ? false : true;
    }

    /**
     * 验证权限
     *
     * @return [type] [description]
     */
    protected function is_auth()
    {
        //$keys = I('user_key','','trim');
        $keys = I('user_key', '', 'trim');
        $data = $this->get_decrypt_key($keys);
        if ($data) {
            $data = explode('_', $data);
            $login_time = $data['0'];
            $this->_id = $data['1'];
            /** 单点登录 */
            $person = new Person();
            $member_info = $person->person_info($this->_id);
            if ($login_time != $member_info['login_time']) {
                /** 给出错误提示信息 */
                $this->apiReturn(array(
                    'status' => '2',
                    'msg' => '您的账号已在另一台设备登录,如非本人操作,请联系管理人员',
                    'info' => ''
                ), 'json');
            }

            $info = get_info('member', array('id' => $this->_id, 'call_api_time' => date('Y-m-d', time())));
            if (!$info['id']) {
                if ($this->_id > 0) {
                    /*记录调用API的最后时间*/
                    $up_api_member_time = array();
                    $up_api_member_time['id'] = $this->_id;
                    $up_api_member_time['call_api_time'] = date('Y-m-d', time());
                    update_data('member', [], [], $up_api_member_time);
                }
            }

        } else {
            $this->set_error('用户密匙错误，请检查参数!');
        }
    }

    /**
     * 获取用户信息
     * @param bool $onlyReturn 为false 时,自动结束请求并返回错误信息
     * @return array|mixed
     */
    protected function get_memberinfo($onlyReturn=true)
    {
        $member_id = I('uid');
        /** 读取缓存*/
        $cache_key = 'member_id_' . I('id');
        $res = F($cache_key);
        if ($res) {
            return $res;
        } else {
//            $field = 'id,article_title,article_headimg,article_author,article_content,article_publish_time,article_img';
            $map = array('uid' => $member_id);
            $res = get_info('member_info', $map, true);
            if(!$res&&!$onlyReturn)ERROR( '用户信息错误');
            F($cache_key, $res);
            return $res;
        }
    }
}