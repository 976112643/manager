<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/9
 * Time: 16:25
 */
namespace Common\Plugin;

/**
 * 头衔管理
 */
class Title extends Base
{
    protected $member_table = 'member_gain_title';
    /**
     * 头衔类型
     * @var array
     */
    protected $type = array(
        array('id'=>'1','title'=>'默认称号'),
        array('id'=>'2','title'=>'累计任务数量'),
        array('id'=>'3','title'=>'累计金额数量'),
    );
    protected function __init()
    {
        $this->config['form'] = array(
            array('func'=> 'type', 'field'=> 'type'),/*验证头衔类型*/
            array('func'=> 'name', 'field'=> 'name'),/*验证头衔名称*/
            array('func'=> 'num', 'field'=> 'num'),/*验证累计数量*/
            array('func'=> 'descript', 'field'=> 'descript'),/*验证描述*/
            array('func'=> 'sort', 'field'=> 'sort'),/*验证排序*/
        );
        $this->table = 'member_title';
    }
    /**
     * 表单内容验证（总函数）
     */
    protected function _form() {
        foreach($this->config['form'] as $key=> $value) {
            call_user_func(array($this, 'check_'.$value['func']), $value['field']);
        }
    }
    /*
	 * 名称验证
	 */
    public function check_name($field) {
        $this->rule[] = array($field, 'require', '头衔名称不能为空');
        $this->rule[] = array($field, '2,10', '头衔名称超出字数为2~10', 0, 'length');
        $this->form[$field] = I('post.'.$field, '');
    }
    /*
	 * 头衔类型验证
	 */
    public function check_type($field) {
        $this->form[$field] = I('post.'.$field, 0, 'int');
        $type = array_column($this->type,'title','id');
        if( !$type[ $this->form[$field] ] ) {
            throw new \Exception('请选择头衔类型');
        }
    }
    /*
	 * 累计数量验证
	 */
    public function check_num($field) {
        $this->rule[] = array($field,'0,9999','累计数量不能超出9999',0,'between');
        $this->form[$field] = I('post.'.$field, '');
    }
    /**
     * 排序
     */
    public function check_sort($field) {
        $this->rule[] = array($field,'0,9999','排序不能超出9999',0,'between');
        $this->form[$field] = I('post.'.$field, 0, 'int');
    }
    /*
	 * 描述验证
	 */
    public function check_descript($field) {
        $this->rule[] = array($field, 'require', '头衔描述不能为空');
        $this->rule[] = array($field, '2,12', '头衔描述超出字数为2~12', 0, 'length');
        $this->form[$field] = I('post.'.$field, '');
    }
    /**
     * ID
     */
    public function check_id($field)
    {
        $this->form[$field] = I('post.'.$field, 0, 'int');
    }
    /**
     * 添加数据
     */
    public function _add(){
        try {
            $M=M();
            $M->startTrans();
            $this->_form();
            $res=update_data($this->table, $this->rule, array(), $this->form);
            if(!is_numeric($res)){
                throw new \Exception($res, 1);
            }
            /** 清除F缓存 */
            $this->clear_table_cache();
        } catch (\Exception $e) {
            $M->rollback();
            return $e->getMessage();
        }
        $M->commit();
        return $res;
    }
    /*
     * 编辑
     */
    public function _edit($map = array()) {
        try {
            $M = M();
            $M->startTrans();
            $config = array(
                array('func'=>'id','field'=>'id'),
            );
            $this->config['form'] = array_merge($this->config['form'],$config);
            $this->_form();
            $result = update_data($this->table, $this->rule, $map, $this->form);
            if(!is_numeric($result)) {
                throw new \Exception($result);
            }
            /** 清除F缓存 */
            $this->clear_table_cache();
        } catch (\Exception $e) {
            $M->rollback();
            return $e->getMessage();
        }
        $M->commit();
        return $result;
    }

    /**
     * 后台获取头衔列表
     * @param $map
     * @return mixed
     */
    public function get_title_data( $map )
    {
        return $this->page($this->table,$map);
    }
    /**
     * 详情
     */
    public function _info($map = array())
    {
        return get_info($this->table,$map);
    }
    /**
     * 头衔类型
     */
    public function _type()
    {
        return $this->type;
    }
    /**
     * 根据头衔ID获取头衔内容
     */
    public function get_data_by_id( $id = '0' )
    {
        $data = get_no_del($this->table);
        return $id ? $data[$id] : $data;
    }
    /**
     * 获取用户头衔
     */
    public function get_user_title( $uid )
    {
        /** 获取用户已拥有的头衔 */
        $res = $this->get_user_all_list( $uid );
        if( $res ){
            $res = array_column($res,'title_id');
        }
        /** 获取全部头衔 */
        $data = $this->get_data_by_id();
        
        /** 判断是否激活该头衔 */
        foreach ($data as $key => $value) {
            $data[$key]['is_onilne'] = '0';
            if(in_array($key, $res)){
                $data[$key]['is_onilne'] = '1';
            }
            unset($data[$key]['is_hid']);
            unset($data[$key]['is_del']);
        }
        return array_values($data);
    }
    /**
     * 获取某用户的头衔
     */
    public function get_user_all_list( $uid )
    {
        /** 获取用户已拥有的头衔 */
        $map = array(
            'uid' =>$uid
        );
        $res = get_result($this->member_table,$map,'','title_id');
        return $res;
    } 
    /**
     * 完成任务后，更新我的头衔
     */
    public function update_user_title( $uid )
    {
        /** 查询当前uid 累计完成的任务数量，累计完成的订单金额 */
        $map = array(
            'seller_id' =>$uid,
            'status' =>'50'
        );
        $field = 'COUNT(`qty`) as sum_count,SUM(`money_total`) as sum_money';
        $res = get_result('order',$map,'',$field);
        $info = reset($res);
        /** 获取我的所有头衔 */
        $titles = $this->get_user_title($uid);
        /** 获取所有我未激活的头衔 */
        $no_online_titles = array();
        foreach ($titles as $key => $value) {
            if( $value['is_onilne'] ) continue;
            $no_online_titles[] = $value;
        }
        /**
         * 生成可以激活的头衔ID
         */
        $new_titles = array();
        foreach ($no_online_titles as $key => $value) {
            if( $value['type'] == '2'){
                /** 判断累计数量 */
                if( $info['sum_count'] >= $value['num'] ){
                    $new_titles[] = $value['id'];
                }
            }
            if( $value['type'] == '3'){
                /** 判断累计金额 */
                if( $info['sum_money'] >= $value['num'] ){
                    $new_titles[] = $value['id'];
                }
            }
        }
        if( $new_titles ){
            /** 更新member_gain_title */
            $_data = array();
            foreach ($new_titles as $key => $value) {
                $a = array();
                $a['uid'] = $uid;
                $a['title_id'] = $value;
                $_data[] = $a;
            }
            try{
                $sql = addSql($_data,$this->member_table);
                execute_sql($sql);
            }catch(\Exception $e){
                return $e->getMessage();
            }
        }
        

    }
    /**
     * 修改我的当前头衔
     * @param $uid
     * @param $key
     * @param $value
     */
    public function edit_title_id($uid,$key,$value)
    {
        $M = M();
        try{
            $map = array('uid'=>$uid,'title_id'=>$value);
            $has = get_info($this->member_table,$map,'id');
            if( $has['id'] <= '0'){
                throw new \Exception("您还未获得该头衔，请继续努力", 1);
            }
            $rule[] = array('title_id','require','头衔ID必须');
            $res = update_data('member_info',$rule,array('uid'=>$uid),array('title_id'=>$value));
            if( !is_numeric($res) ){
                throw new \Exception($res, 1);
            }
            $title = $this->get_data_by_id($value);
            /** 更新Redis缓存 */
            $person = new Person();
            $person->update_redis_value($uid,$key,$value);
            $person->update_redis_value($uid,'title',$title['name']);
        }catch (\Exception $e){
            $M->rollback();
            return $e->getMessage();
        }
        $M->commit();
        return $res;
    }
}