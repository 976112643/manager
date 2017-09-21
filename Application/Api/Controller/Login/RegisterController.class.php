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
		$map = array('device'=>I('device'));
		$info = get_info($this->info_table,$map,true);
		if($info){
			//ERROR('已存在');
			 SUCCESS($info);
		}
        $model = M($table);
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
            'is_extract'		=>	1,
			
        );
        $res = update_data($this->table,[],[],$data);
        if( is_numeric($res) ){
            /** 更新member_info表 */
            $rules = array(
                //array('nickname','require','请确认用户名',1),
                //array('nickname','1,20','用户名不得超过20位',1,'length'),
                //array('head_img','require','头像必须',1),
            );
            /** 检查用户昵称是否重复，如果重复就加后缀 */
            $map = array(
                'nickname' =>array(
                    'like','%'.$posts['nickname'].'%'
                ),
				
            );
            $count = count_data($this->info_table,$map);

            $_data = array(
                'uid' 		=>	$res,
                'head_img'  =>	$posts['head_img'],
				'device'			=>	I('device')
            );
            $_data['nickname'] = $count ?  $posts['nickname'] .$count : $posts['nickname'];


            $_res = update_data($this->info_table,$rules,[],$_data);
            /** 更新member_gain_title表 */
           // update_data('member_gain_title',[],[],array('uid'=>$res,'title_id'=>1));

            /** 添加注册人数 */
           // count_member_day('10');
		    $model->commit();
			 $info = get_info($this->info_table,array('id'=>$_res),true);
			 SUCCESS($info);
          
        }
        $model->rollback();
        $this->set_error('获取用户信息失败');
    }
}

?>