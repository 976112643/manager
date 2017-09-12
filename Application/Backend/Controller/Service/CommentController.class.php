<?php
namespace Backend\Controller\Service;
use Backend\Controller\Service\IndexController;
use Common\Plugin\Comment;
/**
 * 员工评论管理
 */
class CommentController extends IndexController {
    /**
     * 表名 订单评论
     * @var string
     */
    protected $table='order_comment';
    protected $comment;
    /**
     * 自动加载
     */
    protected function _init(){
        $this->comment = new Comment();
    }
    
	/**
	 * 订单列表
	 * @return [type] [description]
	 */
	public function index()
    {
	    $data = $this->get_list();
        $this->assign($data);
		$this->display();
	}
    /**
     * 获取查询条件
     * @return [type] [description]
     */
    protected function get_map()
    {
        $map = $this->default_map('d.detail_title | a.content');
        $get = I('get.');
        if($get['type']!=''){
            $map['a.type']=intval($get['type']);
        }
        if( $get['nickname'] !=''){
            $map['c.nickname'] = array('like','%'.$get['nickname'].'%');
        }
        return $map;
    }
    /**
     * 获取数据集
     * @param   $[method] [获取方法]
     * @time 2016-10-28
     * @author 陶君行<Silentlytao@outlook.com>
     */
    protected function get_list($method = 'page')
    {
        $map = $this->get_map();
        switch ($method) {
            case 'page':
                $res = $this->comment->get_title_data( $map );
                break;
            
            default:
                # code...
                break;
        }
        return $res;
    }
    /**
     * 查看详情
     * @author 鲍海
     * @time 2017-02-14
     */
    public function show(){
        
        $comment_id =I('ids','0','int');  
        $data['info'] = $this->comment->_info( array('id'=>$comment_id) );
        
        $this->assign($data);
        $this->display();
        
    }
    /**
     * 删除图片
     * @return [type] [description]
     */
    public function del_img()
    {
        $ids = I('id');
        if( $ids < 1) $this->error('删除失败');
        $info = get_info('order_comment_image',array('id'=>$ids));
        @unlink($info['image']);
        delete_data('order_comment_image',array('id'=>$ids));
        $this->success('删除成功');
    }
	

    /**
     * 导出
     * @author 鲍海
     * @time 2017-03-07
     */
    public function export(){
        
        $get=I('get.');
        $map['pid']=0;
         /**店铺ID*/
        if(strlen(trim(I('shop_id')))) {
            $map['shop_id'] = array('like','%' . trim(I('shop_id')) . '%');
        }
        
         /**员工姓名*/
        if(strlen(trim(I('keyword')))) {
            $map['shopper_realname'] = array('like','%' . trim(I('keyword')) . '%');
        }
     
        if($get['type']!=''){
            $map['type']=intval($get['type']);
        }
        
        /**省*/
        if(strlen(I('province'))){
            $map['province'] = I('province');
        }
        
        /**市*/
        if(strlen(I('city'))){
            $map['city'] = I('city');
        }
        
        $start=!empty($get['start'])?$get['start']:date('Y-m-d H:i:s',0);
        $end=!empty($get['end'])?$get['end'].' 23:59:59':date('Y-m-d H:i:s',time());
        $map['add_time']  = array('between',$start.','.$end);
        $field='id,service_id,member_id,cover,content,province,city,star_rating_service,type,star_rating_profession,star_rating_environment,shopper_cover,work_number,shopper_realname,shopper_title,add_time';
        $data['list']=get_result(D('ServiceCommentView'),$map,'add_time desc',$field,6);
        
        if($data['list']){
            $_id=array_column($data['list'],'id');
            $photo_list=$this->get_comment_photo($_id);
            $reply_list=$this->get_replay($_id);

            $member_info=$this->get_member_info();
            foreach ($data['list'] as $key => $value) {
                $member_id=$value['member_id'];
                $nickname=!empty($member_info[$member_id]['nickname'])?$member_info[$member_id]['nickname']:sub_str($member_info[$member_id]['mobile'],'****','3','4');
                $value['nickname']=$nickname;
                $value['cover_url'] = $this->_host.$value['cover'];
                foreach ($photo_list as $k => $v) {
                    if($v['comment_id']==$value['id']){
                        $v['url'] = $this->_host.$v['image'];
                        $value['images'][]=$v;
                    }
                }
                if($reply_list){
                    foreach ($reply_list as $k => $v) { 
                        if($v['pid']==$value['id']){
                            $value['reply']['seller_id']=$v['seller_id'];
                            $value['reply']['shop_title']=$v['shop_title'];
                            $value['reply']['content']=$v['content'];
                            $value['reply']['add_time']=$v['add_time'];
                        }
                    }
                }
                switch($value['type']){
                    case 0:
                        $value['type_text'] = '好评';
                    break;
                    case 1:
                        $value['type_text'] = '中评';
                    break;
                    case 2:
                        $value['type_text'] = '差评';
                    break;                            
                }
                
                $arr[]=$value;

                
                
            }
            $data['list']=$arr;
        }
        
        /*表头*/
        $config = array(
             array(
                 'title' => '评价人昵称',
                 'field' => 'nickname',
                 'width' => '15', //单元格宽度
                 ),
             array(
                 'title' => '评价时间',
                 'field' => 'add_time',
                 'width' => '15', //单元格宽度
                 ),
             array(
                 'title' => '评价内容',
                 'field' => 'content',
                 'width' => '15', //单元格宽度
                 ),
             array(
                 'title' => '评价等级',
                 'field' => 'type_text',
                 'width' => '20' //单元格宽度
             ),
             array(
                 'title' => '员工工号',
                 'field' => 'work_number',
                 'width' => '20' //单元格宽度
             ),
              array(
                 'title' => '评价员工',
                 'field' => 'shopper_realname',
                 'width' => '20' //单元格宽度
             ),
 
        );
        /*数据集以及表名*/
        $data['sheetName'] = '评价列表';
        $data['result'] = $data['list'];
        //dump($data);die;
        $this->export_data($config,$data);
    }
   
}