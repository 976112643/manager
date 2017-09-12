<?php
namespace Backend\Controller\Sms;

/**
 * 帮助中心分类
 * 
 * @author 秦晓武
 *         @time 2016-06-07
 */
class RecordController extends IndexController
{

    /**
     * 表名
     * 
     * @var string
     */
    protected $table = 'sms_record';
    /**
     * 模板表
     * @var unknown
     */
    protected $table_template = 'sms_template';

    /**
     * 列表函数
     */
    public function index()
    {
        $map = array();
        /**
         * 禁用
         */
        if (strlen(I('is_hid'))) {
            $map['is_hid'] = I('is_hid');
        }
        /**
         * 关键字
         */
        if (strlen(trim(I('keywords')))) {
            $map['mobile'] = array(
                'like',
                '%' . I('keywords') . '%'
            );
        }
        /**
         * 模版
         */
        if (strlen(trim(I('template')))) {
            $map['template'] = array(
                'like',
                '%' . I('template') . '%'
            );
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
        
        $result = $this->page($this->table, $map, 'add_time desc');
        array_walk($result['list'], function (&$item) {
            $item['template_text'] = array_to_crumbs(get_no_del($this->table_template), $item['template'], array(
                'field' => array(
                    'self' => 'code'
                )
            ));
        });
        $template = array_to_select(get_no_del($this->table_template), I('template'), array(
            'field' => array(
                'self' => 'code'
            )
        ));
        $this->assign($result);
        $this->assign('template', $template);
        $this->display();
    }
}
