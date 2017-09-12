<?php
namespace Api\Controller\Refund;
use Api\Controller\Base\BaseController;
use Common\Help\PushHelp;
use Common\Help\Geohash;

/**
 * 订单超期退款
 * @author 鲍海
 * @time 20170413
 */
class PaypushController extends BaseController
{
    /**
     * 订单超期退款
     * @author 鲍海
     * @time 2017-04-13
     */
    public function index(){
        $debug_data['time'] = date('Y-m-d',time());
        write_debug($debug_data,'订单超期退款');
        /*获取未接单，截止日期时间是昨天的任务*/
        $map = array(
            'door_time' => array('ELT',$debug_data['time']),
            'status'=>array('in',array('20','22'))
        );     
        $demand_result = get_result(D('DemandOrderView'),$map);
        if(!$demand_result)return;

        //开始事务
        $M=M();
        /*开始事务*/
        $M->startTrans();
        $order_ids = [];
        $demand_ids = [];
        $member = M("member"); // 实例化User对象
        foreach($demand_result as $row){
            $order_ids[] = $row['id'];
            $order_data[]=array(
                'order_no' =>$row['order_no'],
                'type' => 24,
                'from_member_id'=> 0,
                'to_member_id' =>$row['member_id'],
                'money' => $row['money_total'],
                'remark'=> '订单超期退款',
                'status' => 99,
                //'add_time'=>date('Y-m-d H:i:s',time())
            );
            /*销售记录*/
            $sale_data[] = array(
                'type'=>1,
                'order_id'=>$row['id'],
                'order_money'=>$row['money_total'],
                'order_no'=>$row['order_no'],
            );
            
            $member->where(array('id'=>$row['member_id']))->setInc('balance',$row['money_total']);
            
        }
        $capital_record_sql =   addSql($order_data,'capital_record');
        $sale_sql =   addSql($sale_data,'sales_log');
        if(count($order_ids)>0){
        
            try {
                /*对应的订单全部修改为交易关闭*/
                $data = array();
                $data['status'] = 90;
                $map = array(
                    'id'=>array('IN',$order_ids)
                );
                $res = update_data('order',[],$map,$data);
                /**增加资金记录*/
                $capital_record_res = execute_sql($capital_record_sql);
                /**添加销售记录*/
                $sale_res = execute_sql($sale_sql);
                //事务提交
                $M->commit();
    
            }catch (\Exception $e) {
                //事务回滚
                $M->rollback();
            }
        
        }
    }
    public function over()
    {
        $debug_data['time'] = date('Y-m-d',time());
        write_debug($debug_data,'任务超期结束');
        /*获取未接单，截止日期时间是昨天的任务*/
        $map = array(
            'door_time' => array('ELT',$debug_data['time']),
            'status'=>array('eq',10)
        );     
        $demand_result = get_result(D('DemandOrderView'),$map);
        if(!$demand_result)return;
        //开始事务
        $M=M();
        /*开始事务*/
        $M->startTrans();
        try{
            $order_ids = array_column($demand_result,'id');
            $data = array();
            $data['status'] = 90;
            $map = array(
                'id'=>array('IN',$order_ids)
            );
            $res = update_data('order',[],$map,$data);
            $M->commit();
        }catch (\Exception $e) {
            //事务回滚
            $M->rollback();
        }
                
    }
    
}

?>