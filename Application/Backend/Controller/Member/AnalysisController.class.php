<?php 
/**
 * 用户详细统计
 */
namespace Backend\Controller\Member;

use Backend\Controller\Count\IndexController;

class AnalysisController extends IndexController
{
	/** 用户ID*/
	private $id;
	protected function _init()
	{
		$this->id = I('ids');
	}
	/**
	 * 提供其他类使用赋值
	 * @param unknown $id
	 */
	public function set_id($id)
	{
		$this->id = $id;
	}
	/**
	 * 列表
	 */
	public function index()
	{
		$data['buy_echar'] = $this->buy_echar();
		$data['captial_echar'] = $this->captial_echar();
		$data['recommend_echar'] = $this->recommend_echar();
		$data['ids'] = $this->id;
		$this->assign($data);
		$this->display();
	}
	/**
	 * 购买频率图表
	 */
	public function buy_echar()
	{
		$type=I('type');
		$data['type'] = $this->_type = in_array($type,['day','week','month','year'])?$type:'month';
		$data['date_list']=$this->date_list();
		$date = json_decode($data['date_list'],true);
		$map['member_id'] = $this->id;
		$map['add_time']=array('between',$this->start_time.','.$this->end_time);
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
		$html = $this->fetch('buy_echar');
		return $html;
	}
	/**
	 * 充值频率图表
	 */
	public function captial_echar()
	{
		$type=I('type');
		$data['type'] = $this->_type = in_array($type,['day','week','month','year'])?$type:'month';
		$data['date_list']=$this->date_list();
		$date = json_decode($data['date_list'],true);
		$map['member_id'] = $this->id;
		$map['add_time']=array('between',$this->start_time.','.$this->end_time);
		$res=M('capital_recharge')->field(true)->where($map)->select();
		$num = count($date);
		for ($i=0;$i<$num;$i++) {
			$_start = strtotime($date[$i]);
			if($i==$num-1){
				/*要向时间后移一个单位，但不知道具体单位，所以设置较大的值*/
				$_end=strtotime($date[$i+1]."+1 ".$this->step);
			}else{
				$_end=strtotime($date[$i+1]);
			}
			$_success[$i]=array();
			$_error[$i] = array();
			if($res){
				foreach ($res as $k => $v) {
					if($_start <= strtotime($v['add_time']) && strtotime( $v['add_time'] )<$_end){
						switch ($v['status']){
							case '10':
								$_error[$i][] = $v;
								break;
							case '20':
								$_success[$i][] = $v;
								break;
							default:
								
								break;
						}
					}
				}
			}
			$user_list['success_total'][] = array_sum(array_column($_success[$i],'total'));
			$user_list['error_total'][] = array_sum(array_column($_error[$i],'total'));
		}
		$count = count($res);
		$data['user_count']  = $count ? $count : 0;
		$data['sum_success_total'] = array_sum($user_list['success_total']);
		$data['sum_error_total'] = array_sum($user_list['error_total']);
		$data['user_list']['success_total']=json_encode($user_list['success_total']);
		$data['user_list']['error_total'] = json_encode($user_list['error_total']);
		$data['_time']=I('time')?I('time'):date('Y-m-d',time());
		$this->assign($data);
		$html = $this->fetch('captial_echar');
		return $html;
	}
	/** 
	 * 推广人员频率报表
	 */
	public function recommend_echar()
	{
		$type=I('type');
		$data['type'] = $this->_type = in_array($type,['day','week','month','year'])?$type:'month';
		$data['date_list']=$this->date_list();
		$date = json_decode($data['date_list'],true);
		$map['pid'] = $this->id;
		$map['add_time']=array('between',$this->start_time.','.$this->end_time);
		$res=M('recommend')->field(true)->where($map)->select();
		$num = count($date);
        $re_recommend = array();
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
						$_member[$i][] = $v['id'];
                        $re_recommend[] = $v['member_id'];
					}
				}
			}
			$user_list['member'][] = count($_member[$i]);
		}
		if(count($re_recommend)){
			$map['pid'] = array('IN',$re_recommend);
	        $map['add_time']=array('between',$this->start_time.','.$this->end_time);
	        $res_child=M('recommend')->field(true)->where($map)->select();
	        //dump($res);
	        $num = count($date);
	        $re_recommend = array();
	        for ($i=0;$i<$num;$i++) {
	            //echo $date[$i].'<br/>';
	            $_start = strtotime($date[$i]);
	            if($i==$num-1){
	                /*要向时间后移一个单位，但不知道具体单位，所以设置较大的值*/
	                $_end=strtotime($date[$i+1]."+1 ".$this->step);
	            }else{
	                $_end=strtotime($date[$i+1]);
	            }
	            //echo '开始时间'.$_start.'<br/>';
	            //echo '结束时间'.$_end.'<br/>';
	            $_rmember[$i]=array();
	            if($res_child){
	                foreach ($res_child as $k => $v) {
	                    if($_start <= strtotime($v['add_time']) && strtotime( $v['add_time'] )<$_end){
	                        $_rmember[$i][] = $v;
	                    }
	                }
	            }
	            $user_list['rmember'][] = count($_rmember[$i]);
	        }
		}

        
        //dump($user_list['rmember']);die;
		$count = count($res) + count($res_child);
		$data['user_count']  = $count ? $count : 0;
		$data['user_list']['member']=json_encode($user_list['member']);
        $data['user_list']['rmember']=json_encode($user_list['rmember']);
		$data['_time']=I('time')?I('time'):date('Y-m-d',time());
		$this->assign($data);
		$html = $this->fetch('recommend_echar');
		return $html;
	}
	/**
	 * 推广人员记录
	 */
	public function recommend()
	{
		$table = 'MemberRecommendView';
		$map = array(
			'pid' =>$this->id,
		);
		$data = $this->page(D($table),$map);
        /** 查询我的所有下家 */
        if($data['list']){
            $ids = [];
            foreach($data['list'] as $row){
                $ids[] =$row['member_id'];
            }
            $map = array(
                'pid'=>array('IN',$ids)
            );
            $res=M('recommend')->field(true)->where($map)->select();
            if($res){
                foreach ($res as $k => $v) {
                    foreach($data['list'] as $key=> $row){
                        if($v['pid']==$row['member_id']){
                            $row['num'] += 1;
                            $data['list'][$key] = $row;
                        }
                    }
                }
            }
        }
        $this->assign('list',$data['list']);
		/** 查询 */
		$this->display();
	}
    
    /**
     * 推广人员记录
     */
    public function re_recommend()
    {
        $table = 'MemberRecommendView';
        $map = array(
            'pid' =>$this->id,
        );
        $data = $this->page(D($table),$map);
        /** 查询我的所有下家 */
        /*if($data['list']){
            $ids = [];
            foreach($data['list'] as $row){
                $ids[] =$row['member_id'];
            }
            $map = array(
                'pid'=>array('IN',$ids)
            );
            $res=M('recommend')->field(true)->where($map)->select();
            if($res){
                foreach ($res as $k => $v) {
                    foreach($data['list'] as $key=> $row){
                        if($v['pid']==$row['member_id']){
                            $row['num'] += 1;
                            $data['list'][$key] = $row;
                        }
                    }
                }
            }
        }
        $this->assign('list',$data['list']);*/
        /** 查询 */
        $this->display();
    }

}


?>