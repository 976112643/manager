<?php
namespace Backend\Controller\System;

/**
 * 配置设置
 * @需求：
 * 1、配置设置
 * @time 2014-12-26
 * 
 * @author 郭文龙 <2824682114@qq.com>
 */
class ConfigController extends IndexController
{

    /**
     * 选择表
     * 
     * @var string
     */
    protected $table = 'config';

    /**
     * 列表
     * 
     * @author 秦晓武
     *         @time 2016-05-31
     */
    public function index()
    {
        $data = $this->page($this->table, $map, 'sort desc');
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
    private function operate()
    {
        $data['info'] = get_info($this->table, array(
            'id' => I('ids')
        ));
        $this->assign($data);
        $this->display('operate');
    }

    /**
     * 单条更新
     */
    private function update()
    {
        $data = I('post.');
        /* 验证参数 */
        $rules[] = array(
            'title',
            'require',
            '请填写配置标题'
        );
        $rules[] = array(
            'name',
            'require',
            '请填写配置标识'
        );
        $rules[] = array(
            'name',
            '/^[a-zA-Z_]{4,20}+$/',
            '配置标识只允许使用字母和下划线'
        );
        $rules[] = array(
            'name',
            '',
            '配置标识已存在，请更换其它标识',
            1,
            'unique'
        );
        $rules[] = array(
            'group',
            'require',
            '请填写分组'
        );
        $rules[] = array(
            'group',
            '/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u',
            '分组只允许使用中文、字母和下划线'
        );
        $rules[] = array(
            'type',
            'require',
            '请选择配置类型'
        );
        $result = update_data($this->table, $rules, array(), $data);
        if (is_numeric($result)) {
            $this->success('操作成功', U('index'));
        } else {
            $this->error($result);
        }
    }
}
