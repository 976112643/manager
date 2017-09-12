<?php
namespace Backend\Controller\Capital;
/**
 * 退单记录
 * @author 秦晓武
 * @time 2016-10-11
 */
class RefundController extends IndexController {
	/**
	 * 充值记录表
	 * @var string
	 */
	protected $table = 'capital_record';
	/**
	 * 起始状态
	 * @var int
	 */
	protected $type_begin = 50;
	/**
	 * 结束状态
	 * @var int
	 */
	protected $type_end = 59;
	/**
	 * 列表
	 */
	public function index(){
		/*查询状态对应表，得到TYPE和STATUS数组*/
		$temp = get_no_del('state_map','id asc');
		$state_list = array();		foreach($temp as $row){
			$state_list[$row['r_table']][$row['r_field']][$row['r_value']] = $row;
		}
		$data['type_list'] = array_filter($state_list[$this->table]['type'],function(&$row){
			return ($row['r_value'] >= $this->type_begin) && ($row['r_value'] <= $this->type_end);
		});
		$data['status_list'] = array_filter($state_list[$this->table]['status'],function(&$row){
			return ($row['r_value'] >= $this->type_begin) && ($row['r_value'] <= $this->type_end);
		});
		
		/*过滤条件*/
		$map = array();		/*关键字*/
		if(strlen(trim(I('keywords')))) {
			$map['m_2.mobile|order_no'] = array('like','%' . trim(I('keywords')) . '%');
		}
		/*类型*/
		$map['_string'] = ' cr.type between ' . $this->type_begin . ' and ' . $this->type_end . ' ';
		if(strlen(trim(I('type')))){
			$map['cr.type'] = I('type');
		}
		/*状态*/
		if(strlen(trim(I('status')))){
			$map['status'] = I('status');
		}
		/*时间*/
		$start = !empty(I('start_date'))?I('start_date'):0;
		$end = !empty(I('stop_date'))?(I('stop_date') . ' 23:59:59'):date('Y-m-d H:i:s');
        if($start&&$end){
		  $map['add_time'] = array('BETWEEN',array($start,$end));
        }
        
		$this->page(D('CRMView'),$map,'id desc');
        //echo D('CRMView')->getLastSql();die;
		$this->assign($data);
		$this->display();
	}
	/**
	 * 锁定
	 */
	public function lock(){
		$this->change_field_value('status',42);
	}
	/**
	 * 取消锁定
	 */
	public function unlock(){
		$this->change_field_value('status',41);
	}
	/**
	 * 审核
	 */
	public function audit(){
		/*查询记录信息*/
		$record_info = get_info('capital_record', array('id' => I('ids')));
		$map_1 = array(
			'id' => $record_info['id'],
			'status' => 50,
		);
		$data_1 = array(
			'status' => 52,
			'opt_info' => json_encode(array(
				'admin_id' => session('member_id'),
				'opt_title' => '审核',
				'opt_time' => date('Y-m-d H:i:s'),
			)),
		);
		$flag = trans(function() use ($map_1,$data_1,$record_info){
			/*更新记录信息*/
			$res = update_data($this->table, array(),$map_1,$data_1);
			if($res == 0){
				return ['状态错误，请刷新重试！'];
			}
			$result[] = $res;
			/*更新用户信息*/
			$result[] = M('member')->where(['u_id' => $record_info['to_member_id']])->setInc('balance',$record_info['money']);
            
            /*插入资金记录*/
            /*$data_2=array(
                'type'=>13,
                'order_no'=>$record_info['order_no'],
                'deal_no'=>build_order_no(),
                'from_member_id'=>0,
                'to_member_id'=>$record_info['to_member_id'],
                'member_id'=>$record_info['to_member_id'],
                'money'=>$record_info['money'],
                'status'=>20,
                'remark'=>'申请退单',
            );
            
            $res2=update_data('member_card_capital_record',[],[],$data_2);/*修改资金记录*/
            
            $order_info = get_info('order',array('order_no'=>$record_info['order_no']));
            
            $data = array();
            $data['status'] = 90; //交易关闭
            $data['id'] = $order_info['id'];
            $res = update_data('order',[],[],$data);
            /**
             * 添加订单操作记录
             */
            $data = array();
            $data = array(
            	'order_id'=>$order_info['id'],
            	'member_id'=>$order_info['member_id'],
            	'admin_id'=>session('member_id'),
            	'remark' =>'用户退单,审核通过'
            );
            update_data('order_log',[],[],$data);
            
            /*销售记录*/
            saller_order_log(1,$order_info['id'],0,$order_info['money_total'],$order_info['order_no']);
            /**
             * 发送微信通知给用户
             */
            /*$open_id = get_info('member',array('id'=>$record_info['to_member_id']),'wechat_open_id');
            $info = array(
            		'tpl_id' =>'14',
            		'openid'=>$open_id['wechat_open_id'],
            		'url' =>U('Home/Index/Account/jiutongka_details',array(),true,true),
            		'data'=>array(
            				'您的退款请求系统已经审核通过',
            				$record_info['order_no'],
            				'用户申请退款',
            				$record_info['money'],
            				'微信',
            		'您支付的金额已经退还到您的九通卡内，点击详情查看资金记录')
            );
            send_message($info,'WX');*/
			return $result;
		});
		if($flag){
			$this->error($flag);
		}
		$this->success('审核成功',U('index'));
	}
	/**
	 * 取消
	 */
	public function cancel(){
		/*查询记录信息*/
		$record_info = get_info('capital_record', array('id' => I('ids')));
		$map_1 = array(
			'id' => $record_info['id'],
			'status' => 50,
		);
		$data_1 = array(
			'status' => 51,
			'opt_info' => json_encode(array(
				'admin_id' => session('member_id'),
				'opt_title' => '取消',
				'opt_time' => date('Y-m-d H:i:s'),
			)),
		);
		$flag = trans(function() use ($map_1,$data_1,$record_info){
			/*更新记录信息*/
			$res = update_data($this->table, array(),$map_1,$data_1);
			if($res == 0){
				return ['状态错误，请刷新重试！'];
			}
			$result[] = $res;
			/*更新用户信息*/
			return $result;
		});
		if($flag){
			$this->error($flag);
		}
		$this->success('取消成功',U('index'));
	}
	/**
	 * 更新后的回调函数
	 * @param string 当前ID
	 * @return string 更新结果
	 * @time 2015-06-14
	 * @author 秦晓武
	 */
	public function call_back_change($id = ''){
		/*获取ID*/
		$map['id'] = strpos(',',$id) ? array('in',$id) : $id;
		/*判断操作*/
		switch(ACTION_NAME){
			case 'lock':
				$title = '锁定';
				break;
			case 'unlock':
				$title = '解锁';
				break;
			default:
				return parent::call_back_change($id);
		}
		/*生成数据*/
		$data['opt_info'] = json_encode([
			'admin_id' => session('member_id'),
			'opt_title' => $title,
			'opt_time' => date('Y-m-d H:i:s'),
		]);
		M($this->table)->where($map)->save($data);
		parent::call_back_change($id);
		return true;
	}
}

