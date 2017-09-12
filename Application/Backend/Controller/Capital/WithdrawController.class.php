<?php
namespace Backend\Controller\Capital;

use Common\Plugin\Pay\Pay;
/**
 * 提现记录
 * 
 * @author 秦晓武
 *         @time 2016-10-11
 */
class WithdrawController extends IndexController
{

    /**
     * 充值记录表
     * 
     * @var string
     */
    protected $table = 'capital_record';

    /**
     * 起始状态
     * 
     * @var int
     */
    protected $type_begin = 40;

    /**
     * 结束状态
     * 
     * @var int
     */
    protected $type_end = 49;

    /**
     * 列表
     */
    public function index()
    {
        /* 查询状态对应表，得到TYPE和STATUS数组 */
        $temp = get_no_del('state_map', 'id asc');
        $state_list = array();
        foreach ($temp as $row) {
            $state_list[$row['r_table']][$row['r_field']][$row['r_value']] = $row;
        }
        $data['type_list'] = array_filter($state_list[$this->table]['type'], function (&$row) {
            return ($row['r_value'] >= $this->type_begin) && ($row['r_value'] <= $this->type_end);
        });
        $data['status_list'] = array_filter($state_list[$this->table]['status'], function (&$row) {
            return ($row['r_value'] >= $this->type_begin) && ($row['r_value'] <= $this->type_end);
        });
        
        /* 过滤条件 */
        $map = array(); /* 关键字 */
        if (strlen(trim(I('keywords')))) {
            $map['m_1.mobile|order_no'] = array(
                'like',
                '%' . trim(I('keywords')) . '%'
            );
        }
        /* 类型 */
        $map['_string'] = ' cr.type between ' . $this->type_begin . ' and ' . $this->type_end . ' ';
        if (strlen(trim(I('type')))) {
            $map['cr.type'] = I('type');
        }
        /* 状态 */
        if (strlen(trim(I('status')))) {
            $map['status'] = I('status');
        }
        /* 时间 */
        $start = ! empty(I('start_date')) ? I('start_date') : 0;
        $end = ! empty(I('stop_date')) ? (I('stop_date') . ' 23:59:59') : date('Y-m-d H:i:s');
        $map['add_time'] = array(
            'BETWEEN',
            array(
                $start,
                $end
            )
        );
        $this->sum_total($map);
        $this->page(D('CRMView'), $map, 'id desc');
        $this->assign($data);
        $this->display();
    }

    /**
     * 锁定
     */
    public function lock()
    {
        $this->change_field_value('status', 42);
    }

    /**
     * 取消锁定
     */
    public function unlock()
    {
        $this->change_field_value('status', 41);
    }

    /**
     * 审核
     */
    public function audit()
    {
        /* 查询记录信息 */
        $record_info = get_info('capital_record', array(
            'id' => I('ids'),
            'status' =>'41'
        ));
        if( !$record_info['id'] ) $this->error('不存在的订单');
        $map_1 = array(
            'id' => $record_info['id'],
            'status' => 41
        );
        $data_1 = array(
            'status' => 46,
            'opt_info' => json_encode(array(
                'admin_id' => session('member_id'),
                'opt_title' => '审核',
                'opt_time' => date('Y-m-d H:i:s')
            ))
        );
        /**
         * 如果是微信提现，提示未开通
         */
        if( $record_info['type'] == '41'){
            $pay = Pay::getInstance('wx'); 
            $res = $pay->transfer($record_info);
            if( !$res['return_code'] ){
                $this->error($res);
            }
            /** 微信付款订单号 */
            $data_1['deal_no'] = $res['payment_no'];
        }
        if($record_info['type']== '42'){
            /** 使用支付宝单笔转账 */
            $pay = Pay::getInstance('ali'); 
            $res = $pay->transfer($record_info);
            if( $res === false ){
                $this->error('支付宝转账失败');
            }
            /** 支付宝订单号 */
            $data_1['deal_no'] = $res->alipay_fund_trans_toaccount_transfer_response->order_id;
        }
        $m = M();
        $m->startTrans();
        try{
            $res = update_data($this->table, array(), $map_1, $data_1);
            if( !is_numeric($res) ){
                throw new \Exception("状态错误，请刷新重试！", 1);
            }
            /*销售记录*/
            saller_order_log(1,$record_info['id'],0,$record_info['charge'],$record_info['order_no']);

        }catch(\Exception $e){
            $m->rollback();
            $this->error($e->getMessage());
        }
        $m->commit();
        $this->success('审核通过',U('index'));
    }

    /**
     * 取消
     */
    public function cancel()
    {
        /* 查询记录信息 */
        $record_info = get_info('capital_record', array(
            'id' => I('ids')
        ));
        $map_1 = array(
            'id' => $record_info['id'],
            'status' => 41
        );
        $data_1 = array(
            'status' => 49,
            'opt_info' => json_encode(array(
                'admin_id' => session('member_id'),
                'opt_title' => '取消',
                'opt_time' => date('Y-m-d H:i:s')
            ))
        );
        $flag = trans(function () use ($map_1, $data_1, $record_info) {
            /* 更新记录信息 */
            $res = update_data($this->table, array(), $map_1, $data_1);
            if ($res == 0) {
                return [
                    '状态错误，请刷新重试！'
                ];
            }
            $result[] = $res;
            /* 更新用户信息 */
            $result[] = M('member')->where([
                'id' => $record_info['from_member_id']
            ])->setInc('balance', $record_info['money']);
            $result[] = M('member')->where([
                'id' => $record_info['from_member_id']
            ])->setDec('frozen', $record_info['money']);
            return $result;
        });
        if ($flag) {
            $this->error($flag);
        }
        $this->success('取消成功', U('index'));
    }

    /**
     * 更新后的回调函数
     * 
     * @param
     *            string 当前ID
     * @return string 更新结果
     *         @time 2015-06-14
     * @author 秦晓武
     */
    public function call_back_change($id = '')
    {
        /* 获取ID */
        $map['id'] = strpos(',', $id) ? array(
            'in',
            $id
        ) : $id;
        /* 判断操作 */
        switch (ACTION_NAME) {
            case 'lock':
                $title = '锁定';
                break;
            case 'unlock':
                $title = '解锁';
                break;
            default:
                return parent::call_back_change($id);
        }
        /* 生成数据 */
        $data['opt_info'] = json_encode([
            'admin_id' => session('member_id'),
            'opt_title' => $title,
            'opt_time' => date('Y-m-d H:i:s')
        ]);
        M($this->table)->where($map)->save($data);
        parent::call_back_change($id);
        return true;
    }
}

