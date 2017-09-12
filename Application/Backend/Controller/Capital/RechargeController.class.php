<?php
namespace Backend\Controller\Capital;

/**
 * 充值记录
 * 
 * @author 秦晓武
 *         @time 2016-10-11
 */
class RechargeController extends IndexController
{
    /** 操作表*/
    protected $table = 'capital_record';
    /** 开始状态*/
    protected $type_begin = 10;
    /** 结束状态*/
    protected $type_end = 19;

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
        $map = array();
        /* 关键字 */
        if (strlen(trim(I('keywords')))) {
            $map['m_2.mobile|order_no|deal_no'] = array(
                'like',
                '%' . I('keywords') . '%'
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
}

