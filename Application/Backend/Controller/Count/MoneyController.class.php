<?php
/**
 * 统计管理-会员统计
 */
namespace Backend\Controller\Count;


class MoneyController extends IndexController
{
    /**
	 * 门店统计
	 */
    public function index()
    {
        $type=I('type');
		$data['type'] = $this->_type = in_array($type,['day','week','month','year'])?$type:'month';
		$data['date_list']=$this->date_list();
		/** 平台总报表 */
    	$data['all'] = $this->all(json_decode($data['date_list'],true),array());
    	/** 平台收入报表 */
    	$data['total'] = $this->total(json_decode($data['date_list'],true),array());
    	/** 平台支出报表 */
    	$data['remove_total'] = $this->remove_total(json_decode($data['date_list'],true),array());

    	$this->assign($data);
        $this->display();
    }
    /**
     * 平台金额报表
     * @param  [type] $date [日期]
     * @param  [type] $map  [查询条件]
     * @return [type]       [description]
     */
    public function all($date,$map)
    {
        $map['add_time']=array('between',$this->start_time.','.$this->end_time);
        $res=M('pingtai_count')->field('SUM(`total`) as total,SUM(`remove_total`) as remove_total,add_time')->where($map)->group('add_time')->select();
        $max = max(array_values(array_column($res, 'total')));
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
            $_remove_member[$i] = array();
            $_diff[$i] = array();
            $_shop_sum[$i] = array();
            if($res){
                foreach ($res as $k => $v) {
                    if($_start <= strtotime($v['add_time']) && strtotime( $v['add_time'] )<$_end){
                        $_member[$i]= $v['total'];
                        $_remove_member[$i] = -$v['remove_total'];
                        $_diff[$i] = $v['total'] - $v['remove_total'];
                    }
                }
            }
            $user_list['total'][] = $_member[$i] ? $_member[$i] : 0;
            $user_list['remove_total'][] = $_remove_member[$i] ? $_remove_member[$i] : 0;
            $user_list['diff'][] = $_diff[$i] ? $_diff[$i] : 0;
        }
        $data['user_count'] = array_sum($user_list['diff']);
        $data['user_list']['total']=json_encode($user_list['total']);   //收入
        $data['user_list']['remove_total']=json_encode($user_list['remove_total']);  //支出
        $data['user_list']['diff']=json_encode($user_list['diff']);   //净额

    	$data['date_list']=$this->date_list();
    	$data['_time'] = I('time') ? I('time') : date('Y-m-d');
    	$this->assign($data);
    	$html = $this->fetch('all');
    	return $html;
    }
    /**
     * 平台收入报表
     * @return [type] [description]
     */
    public function total($date,$map)
    {
    	$one = $this->count_data('pingtai_count', 'total', $map, $date,'member','add_time','datetime','user_count');
    	$data['user_count'] = $one['user_count'];
    	$data['user_list']['member'] = $one['member'];
    	$data['date_list']=$this->date_list();
    	$data['_time'] = I('time') ? I('time') : date('Y-m-d');
    	$this->assign($data);
    	$html = $this->fetch('total');
    	return $html;
    }
    /**
     * 平台支出报表
     * @return [type] [description]
     */
    public function remove_total($date,$map)
    {
    	$one = $this->count_data('pingtai_count', 'remove_total', $map, $date,'member','add_time','datetime','user_count');
    	$data['user_count'] = $one['user_count'];
    	$data['user_list']['member'] = $one['member'];
    	$data['date_list']=$this->date_list();
    	$data['_time'] = I('time') ? I('time') : date('Y-m-d');
    	$this->assign($data);
    	$html = $this->fetch('remove_total');
    	return $html;
    }

}
?>