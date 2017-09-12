<?php
namespace Api\Controller\Order;

use Api\Controller\Base\AuthController;
use Common\Plugin\Comment;
use Common\Plugin\CommentTag;
/**
 * 订单管理-评价管理
 */
class CommentController extends AuthController
{
	/**
	 * 发布评价
	 */
	public function send()
	{
		$_POST['seller_id'] = $this->_id;
		$comment = new Comment();
		$res = $comment->_add();
		if( is_numeric($res) ){
			$this->set_success('发布评价成功',$res);
		}else{
			$this->set_error($res);
		}
	}
	/**
	 * 获取评价标签列表
	 */
	public function get_tag_list()
	{
		$comment = new CommentTag();
		$res = $comment->get_data_by_id();
		foreach ($res as $key => $value) {
			$field = 'id,name';
			$res[$key] = $this->pick_char($value,$field);
		}
		if( $res ){
			$res = array_values($res);
		}else{
			$res = array();
		}
		$this->set_success('ok',$res);
	}
}