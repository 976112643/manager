<?php
namespace Common\Plugin;

use Common\Help\RedisHelp;
/**
 * 基础插件-积分管理类
 * 提供给其他类调用
 */
class Integral extends Base
{
	/** @var [type] [用户ID] */
	private $uid;
	/** @var [type] [积分总表] */
	private $integral_table = 'integral_day';
	/** @var [type] [人员详情表] */
	private $member_table;
	/** @var [type] [人员积分记录表] */
	private $member_log_table = 'member_integral';
	/** @var [type] [积分类型] */
	private $integral_type = array(
			'10' =>'完成任务',
			'20' =>'发布评论',
		);
	/** @var [type] [获得积分数量] */
	private $integral_type_number;
	/** @var [type] [操作类型  1：增加 2：减少] */
	public $operate_type = 1;
	/**
	 * 构造方法
	 * @param [type] $uid [人员ID]
	 */
	public function __construct($uid)
	{
		parent::__construct();
		if (empty($uid)) throw new \Exception('用户id必须', 1);
		$this->uid = $uid;
		$this->integral_type_number = array(
				'10' => intval(C('OVER_ORDER_INTEGRAL')),
				'20' => intval(C('COMMENT_INTEGRAL'))
		);
	}
	/**
	 * 增加完成任务积分
	 * @param [int] $cid [完成的任务ID]
	 */
	public function add_order_num($cid)
	{
		return $this->_add_num(10,$cid);
	}
	/**
	 * 增加发布评论积分
	 * @param [int] $cid [发布评论ID]
	 */
	public function add_comment_num($cid)
	{
		return $this->_add_num(20,$cid);
	}
	/**
	 * 获取一段时间内的积分数
	 * @param  [type] $date [日期]
	 * $date = array(
	 * 	'start' =>'2017-04-26',
	 * 	'end' =>'2017-05-02'
	 * );
	 * @return [type]       [description]
	 */
	public function get_date_integral($date)
	{
		$map = array(
			'day' =>array(
				array('egt',$date['start']),
				array('elt',$date['end'])
			),
			'uid' =>$this->uid
		);
		$res = get_result($this->integral_table,$map,'','day,integral');
		$res = array_column($res,'integral','day');

		/** 组合数据 */
		$start = strtotime($date['start']);
		$end = strtotime($date['end']);
		$data = array();
        for ($i = $start; $i <= $end; $i += 86400){
        	$day = date('Y-m-d', $i);
            $data[] = array(
            	'day' =>$day,
            	'integral'=>$res[$day] ? $res[$day] : 0,
            );
        }
        return $data;
	}
	/**
	 * 获取历史成就值
	 * @return [type] [description]
	 */
	public function get_history_integral()
	{
		$table = $this->_get_table();

		$map = array(
			'uid' =>$this->uid
		);
		$field = 'type,integral_type,num,create_time';
		$res = $this->page($table,$map,'create_time desc,id asc',$field);
		if($res['list']){
			array_walk($res['list'], function(&$a){
				$b['type'] = $a['type'];
				$b['remark'] = $this->integral_type[$a['integral_type']];
				$b['time'] = date('Y-m-d H:i:s',$a['create_time']);
				$b['num'] = $a['num'];
				$a = $b;
			});
		}
		return $res['list'];
	}
	/**
	 * 返回积分类型对应的积分
	 * @param  [type] $integral_type [积分类型]
	 * @return [type]                [分数]
	 */
	public function _return_num($integral_type)
	{
		return $this->integral_type_number[$integral_type];
	}
	/**
	 * 增加积分具体操作
	 * 第一步：获取对应操作的积分
	 * 第二步：生成插入的数据
	 * 第三步：增加MYSQL记录
	 * 第四步：增加|减少人员总积分
	 * 第五步：汇总到REDIS当天内，第二天使用定时任务去汇总到昨日统计表
	 * @param [type] $integral_type [积分类型]
	 */
	private function _add_num($integral_type,$cid = 0)
	{
		$model = M();
		try{
			$model->startTrans();
			//第一步：获取对应操作的积分
			$num = $this->_return_num($integral_type);
			//第二步：生成插入的数据
			$data = $this->_create_data($integral_type,$num,$cid);
			//第三步：增加记录
			$table = $this->_get_table();
			$res = update_data($table,[],[],$data);
			if( !is_numeric($res) ){
				throw new \Exception("积分更新失败", 1);
			}
			//第四步：增加|减少人员总积分
			$this->set_user_integral($num);

		}catch (\Exception $e) {
			$this->_return_exception($e);
			$model->rollback();
			return false;
		}
		$model->commit();

		return $res;
	}
	/**
	 * 插入当天的Redis内,用于汇总
	 * @param [type] $data [数据数组]
	 */
	private function _update_redis_num($data)
	{
		$redis = RedisHelp::getInstance();
		$cache = 'bbs_Integral:'.$data['year'].'_'.$data['month'].'_'.$data['day'];
		$operate = $this->_return_operate();
		return $redis->zadd_zincrby( $cache, $operate .$data['num'], $data['uid'] );
	}
	/**
	 * 获取积分分表
	 */
	private function _get_table()
	{
		$table = $this->member_log_table;
		// $table = find_user_tb($this->uid,$parent_table);
		// create_child_table($table,$parent_table);
		return $table;
	}
	/**
	 * 创建需要插入的数据
	 * @param  [type] $integral_type [积分类型]
	 * @param  [type] $num           [积分数量]
	 * @param  [type] $cid           [操作ID]
	 * @return [array]                [数据数组]
	 */
	private function _create_data($integral_type,$num,$cid)
	{
		//备注
		$operate = $this->_return_operate();
		$remark = $this->integral_type[$integral_type] . $operate .$num;
		$data = array(
			'type' =>$this->operate_type,
			'uid' =>$this->uid,
			'integral_type' =>$integral_type,
			'num' =>$num,
			'cid' =>$cid,
			'year' =>date('Y'),
			'month'=>date('m'),
			'day'  =>date('d'),
			'create_time'=>NOW_TIME,
			'remark' =>$remark,
		);
		return $data;
	}
	/**
	 * 返回具体操作，是加还是减
	 * @return [type] [description]
	 */
	private function _return_operate()
	{
		return $this->operate_type == 1 ? '+' : '-';
	}
	/**
	 * 根据操作符增减用户总积分
	 * @param [type] $num [description]
	 */
	protected function set_user_integral($num)
	{
		$uid = $this->uid;
		if($this->operate_type == '1'){
			M('member_info')->where(array('uid'=>$uid))->setInc('integral',$num);
		}
		if($this->operate_type == '2'){
			M('member_info')->where(array('uid'=>$uid))->setDec('integral',$num);
		}
		/** 更新缓存 */
		$info = get_info('member_info',array('uid'=>$uid),'integral');
		$person = new Person();
		$person->update_redis_value($uid,'integral',$info['integral']);
	}
	/**
	 * 返回异常错误
	 * @param  [type] $e [异常错误]
	 * @return [type]    [description]
	 */
	protected function _return_exception($e)
	{
		$data = array(
			'积分类操作异常',$e
		);
		exception_return($data);
	}
}
?>