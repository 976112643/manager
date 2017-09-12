<?php
/**
 *	地区管理
 */
namespace Backend\Controller\System;

/**
 * 地区管理
 */
class AreaController extends IndexController
{

    /**
     * 选择表
     * 
     * @param $table 选择表            
     */
    protected $table = 'area';

    /**
     * 列表页
     */
    public function index()
    {
        if (I('pid')) {
            $map['pid'] = intval(I('pid'));
        }
        
        $title = I('title');
        if (strlen(trim(I('title')))) {
            $map['title'] = array(
                'like',
                "%" . trim(I('title')) . "%"
            );
        }
        $result = $this->page($this->table, $map, 'id asc');
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
        $info['pid'] = array_to_select(get_no_del($this->table), $info['pid']);
        $data['info'] = $info;
        $this->assign($data);
        $this->display('operate');
    }

    /**
     * 添加/修改操作
     */
    public function update()
    {
        $data = I('post.');
        /* 验证参数 */
        $rules[] = array(
            'title',
            'require',
            '标题必须填'
        );
        $result = update_data($this->table, $rules, array(), $data);
        if (is_numeric($result)) {
            $this->success('操作成功', U('index'));
        } else {
            $this->error($result);
        }
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
        $base_file = 'Public\Plugins\super_level\conf\area_data.js';
        /* 自动创建目录 */
        $log_dir = dirname($base_file);
        if (! is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        file_put_contents($base_file, 'var area_data = ' . json_encode(get_no_del($this->table)));
        parent::call_back_change($id);
    }
}