<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/9
 * Time: 16:25
 */
namespace Common\Plugin;
use Common\Help\ImgHelp;
/**
 * 评论管理
 */
class Comment extends Base
{
    protected $model = 'ServiceCommentView';
    protected $img_table = 'order_comment_image';
    protected function __init()
    {
        $this->config['form'] = array(
            array('func'=> 'order_id', 'field'=> 'order_id'),/*验证订单ID*/
            array('func'=> 'star_rating', 'field'=> 'star_rating'),/*验证评分*/
            array('func'=> 'content', 'field'=> 'content'),/*验证评价内容*/
            array('func'=> 'tag_id', 'field'=> 'tag_id'),/*验证评价标签ID*/
            array('func'=> 'is_anonymous', 'field'=> 'is_anonymous'),/*验证是否匿名*/
            array('func'=> 'time', 'field'=> 'time'),/*验证评价时间*/
            array('func'=> 'is_img', 'field'=> 'is_img'),/*验证是否有图片*/
        );
        $this->table = 'order_comment';
    }
    /**
     * 返回MODEL
     * @return [type] [description]
     */
    public function get_model()
    {
        return D($this->model);
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
	 * 验证订单ID
	 */
    public function check_order_id($field) {
        $this->form[$field] = I('post.'.$field, '');
        if( empty($this->form[$field]) ){
            throw new \Exception("订单ID参数错误", 1);
        }
        /** 查询该订单是否已经完成 */
        $map = array(
            'id' =>$this->form[$field]
        );
        $_field = 'id,status,seller_id,member_id';
        $info = get_info('order',$map,$_field);
        $status = array('40','45','50','99');

        if( !in_array($info['status'], $status)){
            throw new \Exception("该订单还未完成或被取消,暂时不能评价", 1);
        }
        $seller_id = I('post.seller_id');
        /** 查询此人有没有评价过 */
        $map = array(
            'order_id' =>$info['id'],
            'comment_id' =>$seller_id,
        );
        $num = count_data($this->table,$map);
        if( $num > 0 ){
            throw new \Exception("您已经评价过了", 1);
        }

        /** 如果评论者ID和发布人ID一致 */
        if( $seller_id == $info['seller_id'] ){
            $this->form['seller_id'] = $info['seller_id'];
            $this->form['member_id'] = $info['member_id'];
        }else{
            $this->form['seller_id'] = $info['member_id'];
            $this->form['member_id'] = $info['seller_id'];
        } 
        $this->form['comment_id'] = $seller_id; 
    }
    /**
     * 验证评分
     */
    public function check_star_rating($field) {
        $this->rule[] = array($field,'0.5,5','订单分数不能超出5分',0,'between');
        $this->form[$field] = I('post.'.$field, 0, 'floatval');
        /** 计算评分等级 */
        $this->form['type'] = $this->cacl_rating_level( $this->form[$field] );
    }
    /**
     * 验证评价内容
     */
    public function check_content($field) {
        $this->rule[] = array($field,'1,90','评价内容不能超过90个字',0,'length');
        $content =  I('post.'.$field, '','trim');  
        $this->form[$field] = $content;
    }
    /**
     * 验证评价标签
     */
    public function check_tag_id($field) {
        $_field = I( 'post.'.$field);
        //throw new \Exception($_field, 1);
        if($_field){
            /** 查询评价标签是否在系统的评价中 */
            $tag = new CommentTag();
            $tag_data = $tag->get_data_by_id();
            $tag_ids = array_keys($tag_data);
            $new_ids = explode(',', trim($_field,','));
            $diff = array_diff($new_ids, $tag_ids);
            if( $diff ){
                throw new \Exception("评价标签错误,请检查您的参数", 1);
            }
        }
        $this->form[$field] = trim($_field,',');
    }
    /**
     * 验证是否匿名
     */
    public function check_is_anonymous($field) {
        $this->rule[] = array($field,'0,1','匿名参数错误',0,'between');
        $this->form[$field] = I('post.'.$field, '0');
    }
    /**
     * 验证评价时间
     */
    public function check_time($field = '') {
        $field = NOW_TIME;
        $this->form['add_time'] = date('Y-m-d H:i:s',$field);
        $this->form['year'] = date('Y',$field);
        $this->form['month'] = date('m',$field);
        $this->form['day'] = date('d',$field);
        $this->form['add_time_r'] = date('Y-m-d',$field);
    }
    /**
     * 验证是否有图片
     */
    public function check_is_img($field) {
        $num = count( $_FILES[ 'comment_img' ]['name'] );
        $this->form[$field] = $num ? '1' : '0';
    }
    /**
     * 上传图片
     */
    public function upload_img( $comment_id )
    {
        $info = api_upload_picture('comment_img','Uploads/Comment/');
        if( !is_array($info) ){
            throw new \Exception($info, 1);
        }

        if( $info['0'] ){
            $img = $info;
        }else{
            $img = array($info);
        }
        $data = array();
        foreach($img as $file){  
            $data[] = array(
                'comment_id' =>$comment_id,
                'image'      =>$file['savepath'].$file['savename'],
                'add_time'=>date('Y-m-d H:i:s',NOW_TIME),
            );
        }
        $res = M($this->img_table)->addAll($data);
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
        $M=M();
        $M->startTrans();
        try {
            
            $this->_form();
            $res = update_data($this->table, $this->rule, array(), $this->form);
            if(!is_numeric($res)){
                throw new \Exception($res, 1);
            }
            $to_member_id = $this->form['member_id'];
            if( $to_member_id ){
                /** 更新用户总评分 */
                $field = 'SUM(`star_rating`) as sum_star_rating,COUNT(`id`) as count_star_rating';
                $sum_comment = get_result('order_comment',array('member_id'=>$to_member_id),'',$field);
                $sum_comment = reset($sum_comment);
                $star_rating = round( $sum_comment['sum_star_rating'] / $sum_comment['count_star_rating'] , 1);
                update_data('member_info','',array('uid'=>$to_member_id),array('start_rating'=>$star_rating));
                $person = new Person();
                $person->update_redis_value($to_member_id,'start_rating',$star_rating);
            }
            if( $this->form['is_img'] ){
                /** 上传评论图片 */
                $this->upload_img($res);
            }
            
            /** 记录评论用户积分 */
            $uid = $this->form['comment_id'];
            $integral = new Integral($uid);
            $res = $integral->add_comment_num( $res );
            if( !is_numeric($res) ){
                throw new \Exception('更新用户积分失败', 1);
            }
            if($this->form['tag_id']){
                /** 更新每个标签的使用数量 */
                $this->add_tag_num( $this->form['tag_id'] );
            }
            
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
        $res = $this->page(D($this->model),$map,'add_time desc,id asc');
        return $res;
    }
    /**
     * 获取评论标签数据
     */
    public function get_tag_data( $arr = array() )
    {
        $tag = new CommentTag();
        foreach ($arr as $key => $value) {
            if($value['tag_id']){
                $arr[$key]['tag_list'] = $tag->get_data_by_ids( explode(',', $value['tag_id']));
            }else{
                $arr[$key]['tag_list'] = array();
            }
            
        }
        return $arr;
    }
    /**
     * 获取评论图片数据
     */
    public function get_comment_img( $arr = array() )
    {
        $img_id = array();
        foreach ($arr as $key => $value) {
            if( $value['is_img'] == '1'){
                $img_id[] = $value['id'];
            }
        }
        if( count($img_id) > 0 ){
            /** 查询评论图片 */
            $map = array(
                    'comment_id' =>array('in',$img_id)
                );
            $data = get_result($this->img_table,$map,'','id,image,comment_id');
            $img = new ImgHelp();
            $_data = array();
            foreach ($data as $k => $v) {
                $a = array(
                    'image' => $v['image'],
                    'image_url' =>file_url($v['image']),
                    'thumb_image' =>show_member_head_img('',$img->app_thumb($v['image'],'950','950'))
                );
                $_data[$v['comment_id']][] = $a;
            }
        }
        foreach ($arr as $key => $value) {
            if( $value['is_img'] == '1'){
                $arr[$key]['comment_img'] = $_data[$value['id']];
            }else{
                $arr[$key]['comment_img'] = array();
            }
        }
        return $arr;
    }
    /**
     * 详情
     */
    public function _info($map = array())
    {
        $info = get_info(D($this->model),$map);
        if( $info['id'] ){
            /** 获取标签内容 */
            $info = $this->get_tag_data( array($info) );
            /** 获取图片数据 */
            $info = $this->get_comment_img($info);
            $info = $info['0'];
        }
        return $info;
    }
    /**
     * 计算评分所属等级（好评，中评，差评）
     */
    public function cacl_rating_level( $rating )
    {
        switch ($rating) {
            case $rating >= 4 && $rating <= 5:
                $level = '1';  //好评
                break;
            case $rating >= 3 && $rating < 4:
                $level = '2';  //中评
                break;
            case $rating > 0 && $rating < 3:
                $level = '3';  //差评
                break;
            default:
                $level = '1';
                break;
        }
        return $level;
    }
    /**
     * 接口-删除评论
     */
    public function del( $id , $uid)
    {
        $m = M();
        try{
            /** 判断这条评论，是不是这个用户发布的 */
            $map = array(
                'id' =>$id,
                'seller_id' =>$uid
            );
            $info = get_info($this->table,$map,'id');
            if( $info['id'] < 1 ) {
                throw new \Exception("该评论不是您发布的，您不能删除评论", 1);
            }
            /** 查询所有评论图片 */
            $map = array(
                'comment_id' => array('eq',$id)
            );
            $img = get_result($this->img_table,$map,'','id,image');
            
            $m->startTrans();
            /** 前台用户删除 评论*/
            $data = array(
                'id' =>$id,
                'is_hid' =>'1',
            );
            $res = update_data($this->table,[],[],$data);
            /** 前台用户删除 图片 */
            $data = array(
                'is_hid' =>'1'
            );
            $_res = update_data($this->img_table,[],$map,$data);
            if( !is_numeric($res) || !is_numeric($_res)){
                throw new \Exception("删除失败", 1);
            }

        }catch (\Exception $e){
            $m->rollback();
            return $e->getMessage();
        }
        $m->commit();
        return $res;
    }
    /**
     * 增加评论标签总数量
     */
    public function add_tag_num( $ids  )
    {
        if( !is_string($ids) ) return ;
        $sql = "UPDATE `sr_order_comment_tag` SET `num`=num+1 WHERE ( `id` IN ( $ids)  )";
        return execute_sql($sql);
        //return M('order_comment_tag')->where( $where )->setInc('num');
    }
}