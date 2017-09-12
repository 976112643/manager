<?php
namespace Backend\Controller\Member;

/**
 * 前台用户管理
 * 
 * @author 秦晓武
 *         @time 2016-06-30
 */
class GemberController extends IndexController
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
        
        /**
         * 姓名
         */
        if (strlen(trim(I('keyword')))) {
            $map['realname'] = array(
                'like',
                '%' . trim(I('keyword')) . '%'
            );
        }
        
        /**
         * 资质类型
         */
        if (strlen(I('certification_type'))) {
            $map['certification_type'] = I('certification_type');
        }
        
        $map['type'] = 20;
        
        $result = $this->page(D('MemberinfoView'), $map);
        $result['list'] = int_to_string($result['list'],array('certification_type'=>array(1=>'装修公司',2=>'师傅'))) ;
                
        $this->assign($result);
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
        $info = get_info('member',array('id'=>$ids));
        $info['login_remark'] = date('Y-m-d H:i:s',$info['login_time']).' | ' . ip_to_location($info['login_ip']);
        $info['register_remark'] = date('Y-m-d H:i:s',$info['register_time']).' | ' . ip_to_location($info['register_ip']);
        if($info){
            /** 银行卡*/
            $info['bank_info'] = get_result('member_bank',array('member_id'=>$ids));
            if($info['bank_info']){
                $bank_ids = array_column($info['bank_info'], 'bank_id');
                $bank_list = get_result('bank',array('id'=>array('in',$bank_ids)),'name,img');
                array_walk($info['bank_info'], function(&$a) use($bank_list){
                    foreach ($bank_list as $v){
                        if($v['id'] == $a['bank_id']){
                            $a['bank_name'] = $v['name'];
                            $a['bank_img'] = $v['img'];
                        }
                    }
                    $a['add_time'] = date('Y-m-d H:i:s',$a['addtime']);
                });
            }
        }
        //echo $member_id;die;
        $certification_info = get_info('member_certification',array('member_id'=>$ids));
        //if(!$info['id']){$this->set_error('尚未提交过资质认证信息，请先去填写');}
        if($info['type']==1){
            $data['company'] = $certification_info['company'];
            $data['number'] = $certification_info['number'];
            $data['province'] = $certification_info['province'];
            $data['city'] = $certification_info['city'];
            $data['area'] = $certification_info['area'];
            $data['address'] = $certification_info['address'];
            $data['realname'] = $certification_info['realname'];
            $data['mobile'] = $certification_info['mobile'];
            $data['bank'] = $certification_info['bank'];
            $data['bank_account'] = $certification_info['bank_account'];
            $data['qualification_certificate'] = $this->_host.$certification_info['qualification_certificate'];
            $data['certificate_of_business'] = $this->_host.$certification_info['certificate_of_business'];
            
        }else{
            $data['realname'] = $certification_info['realname'];
            $data['mobile'] = $certification_info['mobile'];
            $data['bank'] = $certification_info['bank'];
            $data['bank_account'] = $certification_info['bank_account'];
            $data['id_card'] = $certification_info['id_card'];
            $data['alipay'] = $certification_info['alipay'];
            $data['id_card_front'] = $this->_host.$certification_info['id_card_front'];
            $data['id_card_reverse'] = $this->_host.$certification_info['id_card_reverse'];
        }
        $data['id'] = $certification_info['id'];
        $data['type'] = $certification_info['type'];
        $data['balance_money'] = $certification_info['balance_money'];
        $data['balance_status'] = $certification_info['balance_status'];

        $this->assign('certificate_info',$data);
        $this->assign('info',$info);
        //dump($info);die;
        $this->assign('base_config',$this->base_config());
        $this->assign('bank_config',$this->bank_config());
        
        $this->display();
    }

    /**
     * 基本信息配置
     */
    protected function base_config()
    {
        $config = array(
                array('title'    =>'用户ID','name'     =>'id','size'     =>15,'callback' =>''),
                //array('title'    =>'open_id','name'     =>'wechat_open_id','size'     =>15,'callback' =>''),
                array('title'    =>'姓名','name'     =>'realname','size'     =>15,'callback' =>''),
                array('title'    =>'手机','name'     =>'mobile','size'     =>15,'callback' =>''),
                array('title'    =>'用户头像','name'     =>'head_img','size'     =>30,'callback' =>''),
                array('title'    =>'注册信息','name'     =>'register_remark','size'     =>15,'callback' =>''),
                array('title'    =>'最后一次登陆','name'     =>'login_remark','size'     =>15,'callback' =>''),
                array('title'    =>'总评分','name'     =>'star_rating','size'     =>15,'callback' =>''),
                array('title'    =>'态度','name'     =>'star_rating_service','size'     =>15,'callback' =>''),
                array('title'    =>'质量','name'     =>'star_rating_profession','size'     =>15,'callback' =>''),
                array('title'    =>'效率','name'     =>'star_rating_environment','size'     =>15,'callback' =>''), 
        );
        return $config;
    }
    /**
     * 银行卡信息配置
     */
    protected function bank_config()
    {
        $config = array(
                array('title'    =>'用户昵称','name'     =>'nickname','size'     =>15,'callback' =>''),
                array('title'    =>'银行卡号','name'     =>'account','size'     =>15,'callback' =>''),
                array('title'    =>'银行卡姓名','name'     =>'username','size'     =>15,'callback' =>''),
                array('title'    =>'银行名称','name'     =>'bank_name','size'     =>30,'callback' =>''),
                array('title'    =>'银行图片','name'     =>'bank_img','size'     =>30,'callback' =>''),
                array('title'    =>'添加时间','name'     =>'add_time','size'     =>30,'callback' =>''),
                 
        );
        return $config;
    }
}

