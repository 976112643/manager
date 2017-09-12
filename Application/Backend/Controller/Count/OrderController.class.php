<?php
/**
 * 统计管理-订单统计
 */
namespace Backend\Controller\Count;


class OrderController extends IndexController
{
	/**
	 * 平台统计
	 */
    public function index()
    {
        $type=I('type');
		$data['type'] = $this->_type = in_array($type,['day','week','month','year'])?$type:'month';
		$data['date_list']=$this->date_list();
    	$this->count_order(json_decode($data['date_list'],true),array());
    	$this->assign($data);
        $this->display();
    }
    
    /**
     * 统计订单
     */
    protected function count_order($date,$map)
    {
        /*已支付订单*/
        $one = $this->count_data('pingtai_count', 'count', $map, $date,'member','add_time','datetime','user_count');
        /*未支付订单*/
        $map['status'] = 10;
        $two= $this->count_data('order', 'qty', $map, $date,'new_order','add_time','datetime','new_order_count');
        $data['user_list'] = array_overlay($one,$two);
        
        /*申请退款订单*/
        $map['status'] = 21;
        $three= $this->count_data('order', 'qty', $map, $date,'refund_order','add_time','datetime','refund_order_count');
        $data['user_list'] = array_overlay($three,$data['user_list']);
        
        /*交易关闭订单*/
        $map['status'] = 90;
        $four= $this->count_data('order', 'qty', $map, $date,'cancel_order','add_time','datetime','cancel_order_count');
        $data['user_list'] = array_overlay($four,$data['user_list']);
        //dump($data);die;
        $this->assign($data);
    }
    /**
     * 充值订单统计
     */
    public function recharge()
    {
        $type=I('type');
        $data['type'] = $this->_type = in_array($type,['day','week','month','year'])?$type:'month';
        $data['date_list']=$this->date_list();
        $map['type'] = array(
            array('egt',21),
            array('elt',23)
        );
        $one = $this->count_data('capital_record', 'qty', $map, json_decode($data['date_list'],true),'member','add_time','datetime','user_count');

        $data['user_list'] = $one;
        $data['num_order'] = array_sum(explode(',', $one['member']));
        $this->assign($data);
        $this->display();
    }
    /**
     * 提现订单统计
     */
    public function withdraw()
    {
        $type=I('type');
        $data['type'] = $this->_type = in_array($type,['day','week','month','year'])?$type:'month';
        $data['date_list']=$this->date_list();
        $map['type'] = array(
            array('egt',41),
            array('elt',42)
        );
        $one = $this->count_data('capital_record', 'qty', $map, json_decode($data['date_list'],true),'member','add_time','datetime','user_count');

        $data['user_list'] = $one;
        $data['num_order'] = array_sum(explode(',', $one['member']));
        $this->assign($data);
        $this->display('recharge');
    }
    /**
     * 支付订单统计
     */
    public function pay()
    {
        $type=I('type');
        $data['type'] = $this->_type = in_array($type,['day','week','month','year'])?$type:'month';
        $data['date_list']=$this->date_list();
        $map['status'] = 20;
        $one = $this->count_data('capital_record', 'qty', $map, json_decode($data['date_list'],true),'member','add_time','datetime','user_count');

        $data['user_list'] = $one;
        $data['num_order'] = array_sum(explode(',', $one['member']));
        $this->assign($data);
        $this->display('recharge');
    }
}
?>