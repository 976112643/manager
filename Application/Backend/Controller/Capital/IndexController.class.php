<?php
namespace Backend\Controller\Capital;

use Backend\Controller\Base\AdminController;
/**
 * 资金主类
 * 
 * @author 秦晓武
 *         @time 2016-10-11
 */
class IndexController extends AdminController
{
	/**
	 * 统计总金额
	 */
	public function sum_total($map)
	{
		$field = 'SUM(`money`) as sum_money,SUM(`money_fee`) as sum_money_fee,SUM(`charge`) as sum_charge';
		$sum = get_result(D('CRMView'),$map,'',$field);
		$this->assign('sum_total',$sum['0']);
	}
}
