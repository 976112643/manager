<?php
namespace Backend\Controller\Member;

use Backend\Controller\Base\AdminController;

/**
 * 用户主类
 * 
 * @author 秦晓武
 *         @time 2016-06-30
 */
class IndexController extends AdminController
{
	/**
	 * 用户统计
	 */
	public function counts()
	{
		$data['buy_echar'] = $this->buy_echar();
		$this->assign($data);
		$this->display('Member/Member/counts');
	}
	/**
	 * 购买频率图表
	 */
	public function buy_echar()
	{
		$type=I('type');
		$data['type']  = in_array($type,['day','week','month','year'])?$type:'month';
		$counts = new \Backend\Controller\Count\IndexController();
		$type=I('type');
		$data['type'] = $counts->_type = in_array($type,['day','week','month','year'])?$type:'month';
		$data['date_list']=$counts->date_list();
		$date = json_decode($data['date_list'],true);
		$map['member_id'] = I('ids');
		$map['add_time']=array('between',$counts->start_time.','.$counts->end_time);
		$res=M('order')->field(true)->where($map)->select();
		$num = count($date);
		for ($i=0;$i<$num;$i++) {
			$_start = strtotime($date[$i]);
			if($i==$num-1){
				/*要向时间后移一个单位，但不知道具体单位，所以设置较大的值*/
				$_end=strtotime($date[$i+1]."+1 ".$this->step);
			}else{
				$_end=strtotime($date[$i+1]);
			}
			$_member[$i]=array();
			if($res){
				foreach ($res as $k => $v) {
					if($_start <= strtotime($v['add_time']) && strtotime( $v['add_time'] )<$_end){
						$_member[$i][] = $v;
					}
				}
			}
			$user_list['member'][] = count($_member[$i]);
		}
		$count = count($res);
		$data['user_count']  = $count ? $count : 0;
		$data['user_list']['member']=json_encode($user_list['member']);
		$data['_time']=I('time')?I('time'):date('Y-m-d',time());
		$this->assign($data);
		$html = $this->fetch('Member/Member/buy_echar');
		return $html;
	}
}

