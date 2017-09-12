<?php
namespace Common\Plugin;

use Common\Help\RedisHelp;
use Common\Help\DateHelp;
use Common\Plugin\Title;
use Common\Help\StrHelp;
/**
 * 个人信息管理类
 */
class Person extends Base
{
    /** @var string [用户详细表] */
    private $info_table = 'member_info';
    /**
     * 用户模型
     * @var string
     */
    protected $model = 'MemberinfoView';
    protected $where = array();

    protected function __init()
    {
        $this->config['form'] = array(
            array('func'=> 'nickname', 'field'=> 'nickname'),/*修改昵称*/
            array('func'=> 'head_img', 'field'=> 'head_img'),/*修改头像*/
            array('func'=> 'gender', 'field'=> 'gender'),/*修改性别*/
            array('func'=> 'mobile', 'field'=> 'mobile'),/*修改手机号*/
            array('func'=> 'photo', 'field'=> 'photo'),/*修改相册*/
            array('func'=> 'signature', 'field'=> 'signature'),/*修改签名*/
            array('func'=> 'skill', 'field'=> 'skill'),/*修改技能*/
            array('func'=> 'hobbies', 'field'=> 'hobbies'),/*修改爱好*/
        );
        $this->table = 'member';
    }

    /**
     * 获取用户基础信息
     * @return [type] [description]
     */
    public function person_info($uid)
    {
        $cache = 'member_list:'.$uid;
        $redis = RedisHelp::getInstance();
        $info = $redis->hmget($cache);
        if( !$info['uid'] ){
            $map = array(
                'uid' =>$uid
            );
            $field = 'uid,mobile,nickname,head_img,signature,gender,age,integral,skill,hobbies,title_id,sum_task_num,sum_money_num,start_rating,constellation,recommend_code,login_time';
            /** 获取用户基础信息 */
            $model = D($this->model);
            $info = get_info($model,$map,$field);
            $sex = array('保密','男','女');
            if( $info ){
                $info['head_img'] = show_member_head_img('',$info['head_img']);
                $info['gender_text'] = $sex[$info['gender']];
                /** 获取头衔 */
                $title = new Title();
                $title_info = $title->get_data_by_id($info['title_id']);
                $info['title'] = $title_info['name'];
            }
        }
        return $info;
    }
    /**
     * 添加用户信息到REDIS中，目前只有头像，昵称，年龄，幼儿园，用户类型，性别,手机号
     */
    public function add_info_to_redis($uid)
    {
        if(!$uid) return array();
        try{
            $redis = RedisHelp::getInstance();
            $info = $this->person_info($uid);

            return $redis->hmset('member_list:'.$uid,$info);
        }catch(\Exception $e){
            exception_return();
        }
    }

    /**
     * 接口-修改年龄
     * @param $uid
     * @param $key
     * @param $value
     */
    public function edit_age($uid,$key,$value)
    {
        list($year,$month,$day) = explode('-', I('age'));
        if( !is_numeric($year) || !is_numeric($month) || !is_numeric($day) || (I('age') > date('Y-m-d'))){
            throw new \Exception("年龄参数不合法", 1);  
        }
        $rule_year = '0,'.date('Y');

        $this->rule[] = array($key,'0,200','年龄超出范围',0,'between');
        $this->rule[] = array($year,$rule_year,'年龄中的年参数不合法',0,'between');
        $this->rule[] = array($month,'1,12','年龄中的月参数不合法',0,'between');
        $this->rule[] = array($day,'1,31','年龄中的天参数不合法',0,'between');
        $this->form[$key] = $value;
        $this->where['uid'] = $uid;
        /** 修改星座 */
        $date = new DateHelp();
        $constellation = $date->get_constellation(I('age'));
        $this->form['constellation'] = $constellation;
        $this->form['birthday'] = I('age');
    }
    /**
     * 接口-修改昵称
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function edit_nickname($uid,$key,$value)
    {
        $map = array(
            'uid' => array('neq',$uid),
            'nickname'=>$value
        );
        $count = count_data($this->info_table,$map);
        if( $count > 0 ){
            throw new \Exception("昵称重复,请重新输入", 1);
        }
        $first =  msubstr( $value, 0, 1 ,'utf-8','');
        if( !preg_match("/[a-zA-Z]/",$first) && !preg_match('/['.chr(0xa1).'-'.chr(0xff).']/',$first) ){
           throw new \Exception("用户名必须以英文或者汉字开头", 1);
        }
        $this->rule[] = array($key,'4,10','昵称长度为4~10位',0,'length');
        $this->rule[] = array($key,'filter_str','名称不可以包含特殊表情符号','0','function');
        $this->where['uid'] = $uid;
        $this->form[$key] = $value;
    }
    /**
     * 接口-修改手机号
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function edit_mobile($uid,$key,$value)
    {
        $this->rule[] = array($key,MOBILE,'手机号格式不正确');
        //$this->rule[] = array($key,'','手机号已经存在',0,'unique');
        $this->where['id'] = $uid;
        $this->form[$key] = $value;
        $this->info_table = $this->table;
    }
    /**
     * 修改头像
     */
    public function edit_head_img( $uid )
    {
        $field = 'headimg';
        $limit = 1;
        $save_path = 'Uploads/Headimg/';
        /** @var [type] [API上传图片] */
        $info = api_upload_picture($field,$save_path,$limit);
        if( !is_array($info) ) return $info;

        /*图片路径*/
        $img  = $info['savepath'].$info['savename'];
        $_POST = array(
            'head_img'  =>$img
        );
        $result = update_data('member_info',[],array('uid'=>$uid));
        $return_img = file_url($img);
        $this->update_redis_value($uid,'head_img',$return_img);
        return array('res'=>$result,'img'=>$return_img);
    }
    /**
     * 修改性别
     * @param $uid
     * @param $gender
     */
    public function edit_gender($uid,$key,$value)
    {
        $this->rule[] = array($key,'0,2','性别参数不合法',0,'between');
        $this->where['uid'] = $uid;
        $this->form[$key] = $value;
    }
    /**
     * 修改相册
     * @param $uid
     * @param $gender
     */
    public function edit_photo($uid,$key,$value)
    {
        $this->rule[] = array($key,'0,2','性别参数不合法',0,'between');
        $this->where['uid'] = $uid;
        $this->form[$key] = $value;
    }

    /**
     * 修改个性签名
     * @param $uid
     * @param $key
     * @param $value
     */
    public function edit_signature($uid,$key,$value)
    {
        $this->rule[] = array($key,'0,30','个性签名长度请保持在0~30位',0,'length');
        $this->where['uid'] = $uid;
        $this->form[$key] = $value;
    }
    /**
     * 修改我的技能
     * @param $uid
     * @param $key
     * @param $value
     */
    public function edit_skill($uid,$key,$value)
    {
        $this->rule[] = array($key,'0,30','我的技能长度请保持在0~30位',0,'length');
        $this->where['uid'] = $uid;
        $this->form[$key] = $value;
    }
    /**
     * 修改我的爱好
     * @param $uid
     * @param $key
     * @param $value
     */
    public function edit_hobbies($uid,$key,$value)
    {
        $this->rule[] = array($key,'0,30','我的爱好长度请保持在0~30位',0,'length');
        $this->where['uid'] = $uid;
        $this->form[$key] = $value;
    }
    /**
     * 修改支付密码
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function edit_pay_password($uid)
    {
        /*将新密码存入member表中*/
        $data['id'] = $uid;
        $data['password_1'] = I('password');
        $data['password_2'] = I('repassword');
        $rules=array(
            array('password_1','6,16','请输入6-16位新密码','0','length'),
            array('password_2','password_1','确认密码与新密码不匹配',0,'confirm'),
        );

        $salt = get_rand_char(6);
        $data['deal_salt'] = $salt;
        $data['deal_password'] = get_md5_password( I('password') ,$salt);
        $result = update_data($this->table, $rules, [], $data);
        return $result;
    }
    /**
     * 修改单个字段
     * @param  [type] $uid   [description]
     * @param  [type] $key   [description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function edit_key_value($uid,$key,$value)
    {
        $M=M();
        try {
            call_user_func_array(array($this, 'edit_'.$key), array($uid,$key,$value));
            
            $M->startTrans();
            $res=update_data($this->info_table, $this->rule, $this->where, $this->form);
            if(!is_numeric($res)){
                throw new \Exception($res, 1);
            }
        } catch (\Exception $e) {
            $M->rollback();
            return $e->getMessage();
        }
        $M->commit();
        $this->update_redis_value($uid,$key,$value);
        return $res;
    }
    /**
     * 更新REDIS内的用户信息
     */
    public function update_redis_value( $uid, $key, $value )
    {
        $redis = RedisHelp::getInstance();
        $name = 'member_list:'.$uid;
        return $redis->hset($name,$key,$value);
    }
    /**
     * 第三方登录，绑定手机号
     */
    public function login_bind_mobile($mobile,$uid)
    {
        /** 查询手机号是否存在 */
        $m = M();
        try{
            $m->startTrans();
            $member_info = get_info($this->table,array('mobile'=>$mobile),'id,mobile,login_time');

            $now_member = get_info($this->table,array('id'=>$uid));
            $data = array();
            if( $member_info['id'] ){
                /** 如果已经存在了原有账户，那么合并两个账户信息 */
                $data = $member_info;
                if( $now_member['weixin_open_id'] ){
                    $data['weixin_open_id'] = $now_member['weixin_open_id'];
                }
                if( $now_member['qq_open_id'] ){
                    $data['qq_open_id'] = $now_member['qq_open_id'];
                }
                if( $now_member['sina_open_id'] ){
                    $data['sina_open_id'] = $now_member['sina_open_id'];
                }

            }else{
                $data = $now_member;
                $data['mobile'] = $mobile;
            }
            $time = $data['login_time'];
            /** 更新member */
            $res = update_data($this->table,[],[],$data);
            if( !is_numeric($res) ){
                throw new \Exception("绑定失败", 1);
            }
            if( $member_info['id'] ){
                /** 删除第三方注册的账号 */
                delete_data($this->table,array('id'=>$uid));
                delete_data($this->info_table,array('uid'=>$uid));
            }
            $data =array(
                'id' =>$res,
                'mobile'=>$mobile,
                'login_time' => $time
            );
            $this->update_redis_value($res,'mobile',$mobile);

        }catch( \Exception $e){
            $m->rollback();
            return $e->getMessage();
        }
        $m->commit();
        return array('res'=>$res,'user_info'=>$data);
    }
    /**
     * 生成推广码
     */
    public function create_recommend_code($uid)
    {
        /** 查询是否存在相同的推广码 */
        $count = 0;
        do{
            $code = StrHelp::randString(1,0).StrHelp::randString(1,1).StrHelp::randString(1,0).StrHelp::randString(1,1).StrHelp::randString(1,0);
            $count += count_data('member',array('recommend_code'=>$code));
        }while ($count = 0);

        return update_data($this->table,[],array('id'=>$uid),array('recommend_code'=>$code));
    }
    /**
     * 增加推广人数
     * 1、确定存在推广码后，记录到recomment记录中
     * 2、增加推广码对应人员的等级推广人数
     */
    public function add_recommend($recommend_code,$user_id)
    {
        $m = M();
        try{
            $table = 'recommend';
            $m->startTrans();
            /** 1、查询推广码是否存在 */
            $map = array('recommend_code' => $recommend_code);
            $member = get_info($this->table,$map);
            if( !$member ){
                throw new \Exception("该推广码不存在", 1);
            }
            if( $member['id'] == $user_id ){
                throw new \Exception("不能推广自己", 1);
            }
            /** 2、存在后，查询该推广码对应人员是否有上级和上上级 */
            $count = count_data($table,array('member_id'=>$user_id));
            if( $count > 0 ){
                throw new \Exception("您已经设置了推广人员，不能再设置", 1);
            }
            $path = array($member['id']);
            $parent_member = $grandpa_member = array();
            $parent_member = get_info($table,array('member_id'=>$member['id']));
            if( $parent_member && $parent_member['pid'] ){
                $path[] = $parent_member['pid'];
                $grandpa_member = get_info($table,array('member_id'=>$parent_member['pid']));
                if( $grandpa_member ){
                    $path[] = $grandpa_member['pid'];
                }
            }
            /** 3、插入推荐记录 */
            $data = array(
                'pid' =>$member['id'],
                'member_id' =>$user_id,
                'path' => implode(',', $path)
            );
            $res = update_data($table,[],[],$data);
            if( !$res ){
                throw new \Exception("更新记录失败", 1);
            }
            /** 4、更新上级用户的level_num */
            M('member')->where(array('id'=>$member['id']))->setInc('level_one_num',1);
            /** 5、更新上上级用户的Level_num */
            if( $parent_member ){
                M('member')->where(array('id'=>$parent_member['pid']))->setInc('level_two_num',1);
            }
            /** 6、更新上上上级用户的level_num */
            if( $grandpa_member ){
                M('member')->where(array('id'=>$grandpa_member['pid']))->setInc('level_three_num',1);
            }
        }catch (\Exception $e){
            $m->rollback();
            return $e->getMessage();
        }
        $m->commit();
        return $res;
    }
}
?>