<?php
/**
 * 统计管理-父类
 */
namespace Backend\Controller\Count;

use Backend\Controller\Base\AdminController;
use Common\Help\HttpHelp;

class IndexController extends AdminController
{
    public $_type;
	public $start_time;
	public $end_time;
	public $date_type = true;
	/**
	 * 日期列表
	 * @流程：
	 * 1、根据日期的类型 分别计算相应的时间轴
	 * 2、总结起始时间，结束时间
	 * @author 邹义来
	 */
	public function date_list($type=''){
		$date_list=array();
		$time=I('time')?I('time'):date('Y-m-d',time());/*当前时间*/
		if(strtotime($time)==false){$this->error("日期格式错误！");}
		$this->start_time=strtotime($time."-1 ".$this->_type);
        /*如果没有则使用传参*/
        if(!$this->_type){
            $this->_type = $type;
        }
		switch ($this->_type) {
			case 'day':
				$this->step='hour';
				for($i=1;$i<=24;$i++){
					$date_list[]=date("Ymd H:i:s",strtotime($time."+".($i-1).'hour'));
				}
				$this->start_time=strtotime($time);
				break;
			case 'week':
				$this->step = 'day';
			    $_last = date('Y-m-d',strtotime($time.'-7 day'));
			    $num = $this->_day($_last,$time);
			    for($i=$num;$i>=1;$i--){
					$date_list[]=date("Y-m-d",strtotime($time."-".($i-1).'day'));
				}
				break;
			case 'month':
				$this->step='day';
				$_last=date("Y-m-d",strtotime($time."-1 ".$this->_type));
				$num=$this->_day($_last,$time);
				for($i=$num;$i>=1;$i--){
					$date_list[]=date("Y-m-d",strtotime($time."-".($i-1).'day'));
				}
				break;
			case 'year':
				$this->step='month';
				for($i=12;$i>=1;$i--){
					$date_list[]=date("Y-m",strtotime($time."-".($i-1).'month'));
				}
				break;
			default:
				$this->error("异常错误！");
				break;
		}
		if($this->date_type){
			$this->start_time=date("Y-m-d H:i:s",$this->start_time);
			$this->end_time=date("Y-m-d H:i:s",strtotime($time.'23:59:59'));
		}else{
			$this->end_time = strtotime(date("Y-m-d H:i:s",strtotime($time.'23:59:59')));
		}
		$date_list=array_values($date_list);
		return json_encode($date_list);
	}

	
	/**
	 * 求两个日期之间相差的天数
	 * (针对1970年1月1日之后，求之前可以采用泰勒公式)
	 * @param  $day1
	 * @param  $day2
	 * @return number 天数
	 */
	public function _day ($day1, $day2){
	  	$second1 = strtotime($day1);
	  	$second2 = strtotime($day2);
	  	if ($second1 < $second2) {
	    	$tmp = $second2;
	    	$second2 = $second1;
	  	  	$second1 = $tmp;
	  	}
	  	return ($second1 - $second2) / 86400;
	}
	/**
	 * 获取年龄
	 * @return [type] [description]
	 */
	public  function get_age($birthday='0'){
		$age = date('Y', time()) - date('Y', strtotime($birthday)) - 1;
		if (date('m', time()) == date('m', strtotime($birthday))){  
		    if (date('d', time()) > date('d', strtotime($birthday))){  
		    $age++;  
		    }  
		}elseif (date('m', time()) > date('m', strtotime($birthday))){  
		    $age++;  
		}  
		return  $age;  
	}
	/**
	 * 平台商户查询条件
	 */
	protected function shop_search_map()
	{
		$member_info = session('member_info');
		/** 店长 */
		if($member_info['role_id'] =='26'){
			$map['shop_id'] = $member_info['shop_id'];
		}else{
			/** 店长以上权限 */
			$shop_id = I('shop_id','0','int');
			if($shop_id){
				$map['shop_id'] = $shop_id;
			}else{
				$map['shop_id'] = reset($this->all_shop())['id'];
			}
		}
		return $map;
	}
}
?>