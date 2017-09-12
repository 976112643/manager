<?php
namespace Backend\Controller\Shop;

use Backend\Controller\Base\AdminController;
/**
 * 订单举报类
 */
class OrderReportController extends AdminController
{
	protected $table = 'order_report';

	protected $model = 'OrderReportView';

	/**
	 * 列表页
	 * @return [type] [description]
	 */
	public function index()
	{
		$this->get_list();
		$this->display();
	}
	/**
	 * 搜索条件
	 */
	public function get_map()
	{
		$get = I('get.','','trim');
		if( $get['mobile'] ){
			$map['mobile'] = array('like','%'.$get['mobile'].'%');
		}
		if( $get['nickname'] ){
			$map['nickname'] = array('like','%'.$get['nickname'].'%');
		}
		if( $get['order_no'] ){
			$map['order_no'] = array('like','%'.$get['order_no'].'%');
		}
		return $map;
	}
	/**
	 * 获取结果集
	 */
	protected function get_list( $method = 'page')
	{
		$map = $this->get_map();
		switch ($method) {
			case 'page':
				$res = $this->page(D($this->model),$map,'add_time desc,id desc');
				$this->assign($res);
				break;
			
			default:
				# code...
				break;
		}
		return $res;
	}
}
?>