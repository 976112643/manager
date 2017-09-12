<?php
/**
 * 统计管理-用户统计
 */
namespace Backend\Controller\Count;

class UserController extends IndexController
{
    public function index()
    {
        /*今日时间*/
        $now_day_time = date('Y-m-d',time());
        
    	$type=I('type');
		$data['type'] = $this->_type = in_array($type,['day','week','month','year'])?$type:'month';
		$this->date_type = false;
		$data['date_list']=$this->date_list();
		$this->count_member(json_decode($data['date_list'],true));
        
        /*获取用户注册量*/
        $new_member_count = get_result('member_count',array('add_time'=>$now_day_time),'','count,type'); 
        $data1 = array_column($new_member_count,'count','type');
        $member_count = $data1['10'] ? $data1['10'] : 0;

        $this->assign('member_count',$member_count);

        
        /*获取用户总注册量*/
        $map = array();
        $all_member_count = get_result('member_count',$map,'','SUM(count) as count ,type','','type'); 
        $data2 = array_column($all_member_count,'count','type');
        $all_member_count = $data2['10'] ? $data2['10'] : 0;

        $this->assign('all_member_count',$all_member_count);
        
        
        /*获取用户地区数据(仅湖北省)*/
        $user_address = get_result(D('MemberAddressView'),array('is_default'=>1,'province'=>1709),'','sum,city','','city');
        
        $city = get_result('area',array('pid'=>1709));
        foreach($city as $row){
            $city[$row['id']] = $row;
        }

        $city_data = array();
        foreach($user_address as $row){
            $city_data[] = array('name'=>$city[$row['city']]['title'],'value'=>(int)$row['sum']);
        }
        
        $this->assign('city_data',json_encode($city_data,JSON_UNESCAPED_UNICODE));
        
		$this->assign($data);
        $this->display();
    }
    /**
     * 统计人员
     */
    protected function count_member($date)
    {
        
        $data = array();
        $map['type'] = 10;
        /*用户端*/
        $one = $this->count_data('member_count', 'count', $map, $date,'member','add_time','time','user_count');

        $data['user_list'] = $one;
        //dump($data);die;
        $this->assign($data);
        
    }
}
?>