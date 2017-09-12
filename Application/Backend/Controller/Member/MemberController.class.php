<?php
namespace Backend\Controller\Member;

use Common\Plugin\Title;
use Common\Plugin\PersonPhoto;

/**
 * 前台用户管理
 * 
 * @author 秦晓武
 *         @time 2016-06-30
 */
class MemberController extends IndexController
{

    /**
     * 表名
     * 
     * @var string
     */
    protected $table = 'member';

    /**
     * 列表函数
     */
    public function index()
    {
        /**
         * 禁用
         */
        if (strlen(I('is_hid'))) {
            $map['is_hid'] = I('is_hid');
        }
        /**
         * 手机
         */
        if (strlen(trim(I('mobile')))) {
            $map['mobile'] = array(
                'like',
                '%' . trim(I('mobile')) . '%'
            );
        }
        /*排序*/
        if(strlen(trim(I('sort')))){
            $order = trim(I('sort')).' '.trim(I('order'));
        }
        
        /*注册时间*/
        $start = !empty(I('start_date'))?I('start_date'):0;
        $end = !empty(I('stop_date'))?(I('stop_date') . ' 23:59:59'):date('Y-m-d H:i:s');
        $map['register_time'] = array('BETWEEN',array( $start,strtotime($end)));
        /**
         * 姓名
         */
        if (strlen(trim(I('keyword')))) {
            $map['realname'] = array(
                'like',
                '%' . trim(I('keyword')) . '%'
            );
        }
        
        $result = $this->page(D('MemberinfoView'), $map,$order);

        $this->assign($result);
        if($result['list']){
            $ids = [];
            foreach($result['list'] as $row){
                $ids[] =$row['id'];
            }
            $map = array(
                'pid'=>array('IN',$ids)
            );
            $res=M('recommend')->field(true)->where($map)->select();
            if($res){
                foreach ($res as $k => $v) {
                    foreach($result['list'] as $key=> $row){
                        if($v['pid']==$row['id']){
                            $row['num'] += 1;
                            $result['list'][$key] = $row;
                        }
                    }
                }
            }
        }
        $this->assign('list',$result['list']);
        $this->display();
    }
    /**
     * 资金记录列表
     */
    public function record()
    {
        /* 查询状态对应表，得到TYPE和STATUS数组 */
        $temp = get_no_del('state_map', 'id asc');
        $state_list = array();
        foreach ($temp as $row) {
            $state_list[$row['r_table']][$row['r_field']][$row['r_value']] = $row;
        }
        $data['type_list'] = array_filter($state_list['capital_record']['type'], function (&$row) {
            return $row;
        });
        $data['status_list'] = array_filter($state_list['capital_record']['status'], function (&$row) {
            return $row;
        });
        
        /* 过滤条件 */
        $map = array();
        /* 关键字 */
        if (strlen(trim(I('keywords')))) {
            $map['order_no|deal_no'] = array(
                'like',
                '%' . I('keywords') . '%'
            );
        }
        $map['from_member_id | to_member_id'] = I('ids');
        /* 状态 */
        if (strlen(trim(I('status')))) {
            $map['status'] = I('status');
        }
        if (strlen(trim(I('type')))) {
            $map['cr.type'] = I('type');
        }
        /* 时间 */
        $start = ! empty(I('start_date')) ? I('start_date') : 0;
        $end = ! empty(I('stop_date')) ? (I('stop_date') . ' 23:59:59') : date('Y-m-d H:i:s');
        $map['add_time'] = array(
            'BETWEEN',
            array(
                $start,
                $end
            )
        );
        $this->page(D('CRMView'), $map, 'id desc');
        $this->assign($data);
        $this->display();
    }

    /**
     * 添加
     * 
     * @author 秦晓武
     *         @time 2016-05-31
     */
    public function add()
    {
        if (IS_POST) {
            $this->update();
        } else {
            $this->operate();
        }
    }

    /**
     * 编辑
     * 
     * @author 秦晓武
     *         @time 2016-05-31
     */
    public function edit()
    {
        if (IS_POST) {
            $this->update();
        } else {
            $this->operate();
        }
    }

    /**
     * 显示
     * 
     * @author 秦晓武
     *         @time 2016-05-31
     */
    protected function operate()
    {
        $info = get_info($this->table, array(
            'id' => I('ids')
        ));
        $data['info'] = $info;
        $this->assign($data);
        $this->display('operate');
    }

    /**
     * 修改
     * 
     * @author 秦晓武
     *         @time 2016-05-31
     */
    protected function update()
    {
        $data = I('post.');
        /* 获取前台传递的添加参数 */
        if ($data['password']) {
            $salt = get_rand_char(6);
            $data['salt'] = $salt;
            $data['password'] = md5(md5($data['password']) . $salt);
        } else {
            unset($data['password']);
        }
        /**
         * 验证参数
         */
        $rules[] = array(
            'mobile',
            '',
            '手机已存在',
            0,
            'unique'
        );
        $rules[] = array(
            'mobile',
            MOBILE,
            '手机格式错误',
            1,
            'regex'
        );
        $rules[] = array(
            'email',
            EMAIL,
            '邮箱格式错误',
            2,
            'regex'
        );
        $rules[] = array(
            'password',
            'require',
            '密码必填',
            1,
            '',
            1
        );
        $result = update_data($this->table, $rules, array(), $data);
        if (is_numeric($result)) {
            $this->success('操作成功', U('index'));
        } else {
            $this->error($result);
        }
    }
    /**
     * 详情
     */
    public function details()
    {
        $ids = I('ids');
        $info = get_info(D('MemberinfoView'),array('id'=>$ids));
        $info['login_remark'] = date('Y-m-d H:i:s',$info['login_time']).' | ' . ip_to_location($info['login_ip']);
        $info['register_remark'] = date('Y-m-d H:i:s',$info['register_time']).' | ' . ip_to_location($info['register_ip']);
        if($info){
            /** 获取用户头衔 */
            $title = new Title();
            $info['title_list'] = $title->get_user_title( $info['id'] );
            $title_info = $title->get_data_by_id($info['title_id']);
            $info['name'] = $title_info['name'];
            /** 获取用户相册 */
            $photo = new PersonPhoto();
            $info['photo_list'] = $photo->my_photo( $info['id']);
            /** 获取用户发布订单数量 */
            $info['send_order_num'] = count_data('order',array('member_id'=>$ids));
            /** 获取用户接受订单数量 */
            $info['receive_order_num'] = count_data('order',array('seller_id'=>$ids));
        }
        $this->assign('info',$info);    
        $this->display('operate');
    }
    /**
     * 获取导出配置
     */
    protected function get_export_config()
    {
         $config = array(
                array('title'    =>'用户ID','name'     =>'id','size'     =>15,'callback' =>''),
                array('title'    =>'姓名','name'     =>'nickname','size'     =>15,'callback' =>''),
                array('title'    =>'手机','name'     =>'mobile','size'     =>15,'callback' =>''),
                array('title'    =>'用户头像','name'     =>'head_img','size'     =>30,'callback' =>''),
                array('title'    =>'注册时间','name'     =>'register_time','size'     =>15,'callback' =>''),
                array('title'    =>'注册IP','name'     =>'register_ip','size'     =>15,'callback' =>''),
                array('title'    =>'最后一次登陆','name'     =>'login_remark','size'     =>15,'callback' =>''),
         );
         return $config;
    }
    /**
     * 发布的订单列表
     */
    public function send_order_list()
    {
        $this->page('order',array('member_id'=>I('id')),'add_time desc,id desc');
        $this->display('order_index');
    }
    /**
     * 接受的订单列表
     */
    public function receive_order_list()
    {
        $this->page('order',array('seller_id'=>I('id')),'add_time desc,id desc');
        $this->display('order_index');
    }
}

