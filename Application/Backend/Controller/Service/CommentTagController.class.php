<?php
namespace Backend\Controller\Service;

use Common\Plugin\CommentTag;
/**
 * 员工评论标签管理
 */
class CommentTagController extends IndexController 
{
	/** @var  标签管理类 */
    protected $_title;
    /**
     * 构造方法
     */
    protected function _init()
    {
        $this->_tag = new CommentTag();
        $this->table = $this->_tag->table;
    }
    /**
     * 列表
     */
    public function index()
    {
        $this->get_list();
        $this->display();
    }
    /**
     * 获取列表页过滤条件，用于和导出公用逻辑
     * @return array 过滤数组
     */
    private function map(){
        $map = $this->default_map('name');
        return $map;
    }
    /**
     * 获取结果集
     */
    protected function get_list($method = 'page')
    {
        $map = $this->map();
        switch ($method) {
            case 'page':
                $res = $this->_tag->get_title_data($map);
                $this->assign($res);
                break;
            default:
                $res = array();
                break;
        }
        return $res;
    }
    /**
     * 添加
     *
     * @author 秦晓武
     *         @time 2016-05-31
     */
    public function add()
    {
        if(IS_POST) {
            $result = $this->_tag->_add();
            if(is_numeric($result)) {
                $this->success('操作成功！', U('index'));
            }
            $this->error($result, U('index'));
        }
        $this->display('operate');
    }

    /**
     * 编辑
     *
     * @author 秦晓武
     *         @time 2016-05-31
     */
    public function edit()
    {
        $id = I('ids', 0, 'int');
        $data['info'] = $this->_tag->_info(array('id'=>$id));
        if(!$data['info']) {
            $this->error('信息不存在');
        }
        if(IS_POST) {
            $result = $this->_tag->_edit();
            if(is_numeric($result)) {
                $this->success('操作成功！', U('index'));
            }
            $this->error($result, U('index'));
        }
        $this->assign($data);
        $this->display('operate');
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
}
?>