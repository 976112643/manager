<?php
namespace Backend\Controller\Service;
use Backend\Controller\Service\IndexController;

class CommentImgController extends IndexController
{
	/**
	 * 获取图片列表
	 * @return [type] [description]
	 */
	public function index()
	{
		$res = $this->page('order_comment_image',array(),'id desc',true,'50');
		$this->assign($res);
		$this->display();
	}
}