<?php
namespace Api\Controller\User;

use Api\Controller\Base\AuthController;
use Common\Plugin\Comment;
use Common\Help\StrHelp;
/**
 * 我的评价管理类
 */
class EvaluationController extends AuthController
{
	/** @var [type] [评价对象] */
	protected $comment;
	protected function __init()
	{
		$this->comment = new Comment();
	}
	/** 
	 * 我发布的评价列表
	 */
	public function my_send_list()
	{
		$map = array(
			'seller_id' =>$this->_id
		);
		$field = 'id,star_rating,order_id,nickname,m_head_img,add_time,detail_title,content,tag_id,is_img';	
		$this->get_my_evaluation($map , $field);
	}
	/**
	 * 我收到的评价列表
	 */
	public function my_receive_list()
	{	
		$map = array(
			'member_id' =>$this->_id
		);
		$field = 'id,star_rating,order_id,seller_nickname,s_head_img,add_time,detail_title,content,tag_id,is_img,is_anonymous';	
		$this->get_my_evaluation($map , $field);
	}
	/**
	 * 我发布的评价-删除该评价
	 */
	public function del_my_send()
	{
		$posts = I('post.');
		if( empty($posts['id']) || !is_numeric($posts['id']) ) $this->set_error('请传入评论ID');
		$res = $this->comment->del( $posts['id'] , $this->_id);
		if( is_numeric($res) ){
			$this->set_success('删除成功',$res);
		}else{
			$this->set_error($res);
		}
	}
	/**
	 * 获取评论列表
	 */
	public function get_my_evaluation( $map = array() ,$field)
	{
		$model = $this->comment->get_model();
		$res = $this->page($model,$map,'add_time desc,id asc',$field);
		if( $res['list'] ){
			array_walk($res['list'], function(&$a){
				$a['s_head_img'] = file_url($a['s_head_img']);
				$a['m_head_img'] = file_url($a['m_head_img']);
				if($a['is_anonymous'] == '1'){
					if(isset($a['seller_nickname'])){
						$a['seller_nickname'] = mb_substr($a['seller_nickname'],0,1,'UTF-8').'****'.mb_substr($a['seller_nickname'],'-1',1,'UTF-8');
					}
				}
			});
			
			/** 获取评价标签内容 */
			$res['list'] = $this->comment->get_tag_data($res['list']);
			/** 获取评价图片内容 */
			$res['list'] = $this->comment->get_comment_img($res['list']);
			$this->set_success('ok',$res['list']);
		}else{
			$this->set_error('暂无数据');
		}	
	}
}
?>