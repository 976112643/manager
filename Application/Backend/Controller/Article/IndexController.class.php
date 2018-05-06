<?php
namespace Backend\Controller\Article;

use Backend\Controller\Base\AdminController;
use Common\Help\BaseHelp;

/**
 * 文章
 */
class IndexController extends AdminController
{
    /** 操作表名*/
    protected $model_name = 'NoteMemberView';
    protected $table='note';
    /**
     * 列表
     *
     */
    public function index()
    {
        trace('本调试信息仅页面Trace中可见');
        $status = I('status');
        $map = $this->default_map('content', 'addtime', true, true);
        $map['status'] = $status ? $status : '0';
        $model = D($this->model_name);
        $this->page($model, $map, 'addtime desc', array(), 30);
        $this->display('Article/Index/index');
    }

//http://localhost/Backend/Article/Index/ueditor.html?action=uploadimage&encode=utf-8

    /**
     * 添加
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

        $ids = I('ids');
        $map = array(
            'id' => $ids
        );
        $info = get_info(D($this->model_name), $map);
        $this->assign('info', $info);
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
        /* 获取前台传递的添加参数 */
        $data['content'] = $_POST['content'];
        /* 验证参数 */
        $rules[] = array(
            'content',
            'require',
            '内容必须填'
        );
        if ($data['id']) {//存在id则直接调取编辑
            $map = array(
                'id' => $data['id'],
            );
            $info = get_info($this->table, $map, true);
            if (!$data['addtime']) $data['addtime'] = millisecond();
            if (!$data['updatetime']) $data['updatetime'] = millisecond();
            $data['version'] = $info['version'] + 1;//版本+1
        }
        $result = update_data($this->table, $rules, array(), $data);
        if (is_numeric($result)) {
            $this->success('操作成功', U('index'));
        } else {
            $this->error($result);
        }
    }




    /**
     * 详情
     */
    public function details()
    {
        $ids = I('ids');
        $map = array(
            'id' => $ids
        );
        $info = get_info(D($this->model_name), $map);
        $this->assign('info', $info);
        $this->display('details');
    }
}

?>