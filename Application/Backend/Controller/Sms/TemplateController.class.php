<?php
namespace Backend\Controller\Sms;

/**
 * 帮助中心
 * 
 * @author 秦晓武
 *         @time 2016-06-01
 */
class TemplateController extends IndexController
{

    /**
     * 表名
     * 
     * @var string
     */
    protected $table = 'sms_template';

    /**
     * 列表函数
     */
    public function index()
    {
        $map = array();
        $result['list'] = get_result($this->table, $map, 'id desc');
        $this->assign($result);
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
        /* 验证参数 */
        $rules[] = array(
            'sign',
            'require',
            '签名名称必须填',
            1
        );
        $rules[] = array(
            'code',
            'require',
            '模版ID必须填',
            1
        );
        $rules[] = array(
            'code',
            '',
            '模版ID已存在',
            1,
            'unique'
        );
        $rules[] = array(
            'title',
            'require',
            '标题必须填',
            1
        );
        $rules[] = array(
            'product',
            'require',
            'product必须填',
            1
        );
        $rules[] = array(
            'content',
            'require',
            '内容必须填',
            1
        );
        $result = update_data($this->table, $rules, array(), $data);
        if (is_numeric($result)) {
            $this->success('操作成功', U('index'));
        } else {
            $this->error($result);
        }
    }
}
