<?php
namespace Backend\Controller\Capital;

/**
 * 支付记录
 * 
 * @author 秦晓武
 *         @time 2016-10-11
 */
class PayController extends IndexController
{
    /** 操作表*/
    protected $table = 'capital_record';
    /** 开始状态*/
    protected $type_begin = 20;
    /** 结束状态*/
    protected $type_end = 29;

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
        /*if (strlen(trim(I('keywords')))) {
            $map['m_2.mobile|order_no|deal_no'] = array(
                'like',
                '%' . I('keywords') . '%'
            );
        }*/
        /* 类型 */
        $map['_string'] = ' cr.type between ' . $this->type_begin . ' and ' . $this->type_end . ' ';
        
        /* 关键字 */
        if (strlen(trim(I('keywords')))) {
            $map['m_1.mobile|order_no|deal_no'] = array(
                'like',
                '%' . trim(I('keywords')) . '%'
            );
        }
        /* 类型 */
        if (strlen(trim(I('type')))) {
            $map['cr.type'] = I('type');
        }
        /* 状态 */
        if (strlen(trim(I('status')))) {
            $map['status'] = I('status');
        }
        /* 时间 */
        if (strlen(trim(I('start_date')))) {
            $map['update_time'] = array(
                'gt',
                trim(I('start_date'))
            );
        }
        if (strlen(trim(I('stop_date')))) {
            $map['update_time'] = array(
                'elt',
                trim(I('stop_date')) . ' 23:59:59'
            );
        }
        $this->sum_total($map);
        $this->page(D('CRMView'), $map, 'id desc');
        $this->assign($data);
        $this->display();
    }
}

