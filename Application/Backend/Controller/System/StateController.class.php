<?php
namespace Backend\Controller\System;

/**
 * 状态设置模块
 * @time 2016-10-12
 * 
 * @author 秦晓武
 */
class StateController extends IndexController
{

    /**
     * 状态配置表
     * 
     * @var string
     */
    protected $table = 'state_map';

    /**
     * 列表
     */
    public function index()
    {
        $data['table_list'] = $this->get_table_list();
        $map = array();
        /* 表名 */
        if (strlen(trim(I('r_table')))) {
            $map['r_table'] = I('r_table');
        }
        $this->page($this->table, $map, 'r_table,r_field,r_value');
        $this->assign($data);
        $this->display();
    }

    /**
     * 添加
     * 
     * @author 秦晓武
     *         @time 2016-05-31
     */
    public function add()
    {
        if (IS_POST) {
            $this->update();
        } else {
            $this->operate();
        }
    }

    /**
     * 编辑
     * 
     * @author 秦晓武
     *         @time 2016-05-31
     */
    public function edit()
    {
        if (IS_POST) {
            $this->update();
        } else {
            $this->operate();
        }
    }

    /**
     * 显示
     * 
     * @author 秦晓武
     *         @time 2016-05-31
     */
    protected function operate()
    {
        $info = get_info($this->table, array(
            'id' => I('ids')
        ));
        $info['table_list'] = $this->get_table_list();
        $data['info'] = $info;
        $this->assign($data);
        $this->display('operate');
    }

    /**
     * 修改
     * 
     * @author 秦晓武
     *         @time 2016-05-31
     */
    protected function update()
    {
        $data = I('post.');
        /**
         * 验证参数
         */
        $rules[] = array(
            'r_table',
            'require',
            '表名必须！'
        );
        $rules[] = array(
            'r_field',
            'require',
            '字段必须！'
        );
        $rules[] = array(
            'r_value',
            'require',
            '值必须！'
        );
        $rules[] = array(
            'title',
            'require',
            '标题必须！'
        );
        $result = update_data($this->table, $rules, array(), $data);
        if (is_numeric($result)) {
            $this->success('操作成功', U('index'));
        } else {
            $this->error($result);
        }
    }

    /**
     * 获取所有表名
     * 
     * @author 秦晓武
     *         @time 2016-10-12
     */
    private function get_table_list()
    {
        $table_list = array();
        $tables = M()->query('Select table_name,table_comment from INFORMATION_schema.tables where table_schema="' . C('DB_NAME') . '"');
        foreach ($tables as $row) {
            $table = substr($row['table_name'], strlen(C('DB_PREFIX')));
            $table_list[$table] = array(
                'id' => $table,
                'title' => $table . ' | ' . $row['table_comment'],
                'comment' => $row['table_comment']
            );
        }
        return $table_list;
    }
}
