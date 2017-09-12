<?php
/**
 * 统计管理-销量统计
 */
namespace Backend\Controller\Count;

use Common\Controller\Shop\Shoplist;

class SaleController extends IndexController
{
    /**
	 * 门店统计
	 */
    public function index()
    {
        $type=I('type');
		$data['type'] = $this->_type = in_array($type,['day','week','month','year'])?$type:'month';
		$data['date_list']=$this->date_list();
    	$map = $this->shop_search_map();
		$this->count_sale(json_decode($data['date_list'],true),$map);
		$res = $this->all_shop();
		$data['shop_list'] = $this->html($res,I('shop_id'),'shop_id','请选择门店');
		$this->assign($data);
        $this->display();
    }
    /**
     * 平台统计
     */
    public function platform()
    {
    	$type=I('type');
		$data['type'] = $this->_type = in_array($type,['day','week','month','year'])?$type:'month';
		$data['date_list']=$this->date_list();
    	$this->count_sale(json_decode($data['date_list'],true),array());
    	$this->assign($data);
        $this->display();
    }
    /**
     * 统计销量
     */
    protected function count_sale($date,$map)
    {
    	$map['add_time']=array('between',$this->start_time.','.$this->end_time);
		$res=M('shop_food_count')->field(true)->where($map)->select();
		$num = count($date);
		
		/**
		 * 折线图折叠
		 * @var Ambiguous $data
		 */
		$list = new Shoplist();
		unset($map['add_time']);
		$food_list = $list->get_one_shop_food($map, 'id asc', true);
		$food_list = array_column($food_list,null,'id');
		$_food_data = array();
		for ($i=0;$i<$num;$i++) {
			$_start = strtotime($date[$i]);
			if($i==$num-1){
				/*要向时间后移一个单位，但不知道具体单位，所以设置较大的值*/
				$_end=strtotime($date[$i+1]."+1 ".$this->step);
			}else{
				$_end=strtotime($date[$i+1]);
			}
			$_member[$i]=array();
			$_food[$i] = array();
			if($res){
				foreach ($res as $k => $v) {
					if($_start <= strtotime($v['time']) && strtotime( $v['time'] )<$_end){
						$_member[$i][]=$v;
						$_food[$i][$v['product_id']] = $v['count'];
					}
				}
			}
			$user_list['member'][] = array_sum(array_column($_member[$i],'count'));
		}
		foreach ($food_list as $vv){
			$_a = array(
					'name'=>$vv['title'],
					'type'=>'line',
					'stack'=>'总量',
			);
			for ($i=0;$i<$num;$i++) {
				if($_food[$i][$vv['id']]){
					$_a['data'][] = $_food[$i][$vv['id']];
				}else{
					$_a['data'][] = 0;
				}
			}
			$_food_data[] = $_a;
		}
		$data['user_count'] = array_sum(array_column($res,'count'));
		$data['user_list']['member']=json_encode($user_list['member']);
		$data['food']['series'] = json_encode($_food_data);
		$data['food']['leg'] = json_encode(array_column($food_list, 'title'));
		$this->assign($data);
    }
}
?>