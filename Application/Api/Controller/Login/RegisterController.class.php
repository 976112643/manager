<?php
namespace Api\Controller\Login;

use Api\Controller\Base\BaseController;
use Api\Controller\Login\Base;

/**
 * 登录类
 */
class RegisterController extends BaseController
{
    protected $info_table = 'member_info';
    protected $table = 'member';

    /**
     * 构造方法
     */
    protected function __init()
    {
        parent::__init();
    }

    public function index()
    {

        $posts = I('post.');
        $map = array('device' => I('device'));
        $info = get_info($this->info_table, $map, true);
        if ($info) {
            $this->update_device_info($info['uid'],$info['id']);//更新设备
            SUCCESS($info);
        }
        $model = M($this->table);
        $model->startTrans();
        /** 更新member表 */
        $data = array(
            'qq_open_id' => $posts['qq_open_id'],
            'weixin_open_id' => $posts['wx_open_id'],
            'sina_open_id' => $posts['sina_open_id'],
            'login_ip' => get_client_ip(),
            'login_time' => time(),
            'register_time' => time(),
            'register_ip' => get_client_ip(),
            'is_extract' => 1,
        );
        $res = update_data($this->table, [], [], $data);
        if (is_numeric($res)) {
            /** 更新member_info表 */
            $rules = array(
            );
            /** 检查用户昵称是否重复，如果重复就加后缀 */
            $map = array(
                'nickname' => array(
                    'like', '%' . $posts['nickname'] . '%'
                ),

            );
            $count = count_data($this->info_table, $map);


            $_data = array(
                'uid' => $res,
                'head_img' => $posts['head_img'],
                'device' => I('device'),
                'device_brand' => I('device_brand'),
                 'device_model' => I('device_model'),
                'device_man' => I('device_man')
            );
            if($posts['nickname']){
                $_data['nickname'] = $count ? $posts['nickname'] . $count : $posts['nickname'];
            }
            $_res = update_data($this->info_table, $rules, [], $_data);
            $model->commit();
            $info = get_info($this->info_table, array('id' => $_res), true);
            SUCCESS($info);

        }
        $model->rollback();
        $this->set_error('获取用户信息失败');
    }

    protected function update_device_info($res,$id){
        $_data = array(
            'id'=>$id,
            'uid' => $res,
            'device' => I('device'),
            'device_brand' => I('device_brand'),
            'device_model' => I('device_model'),
            'device_man' => I('device_man')
        );
        $_res = update_data($this->info_table, null, [], $_data);
    }
}

?>