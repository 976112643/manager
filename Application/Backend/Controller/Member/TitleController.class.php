<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/9
 * Time: 16:02
 */
namespace Backend\Controller\Member;

use Backend\Controller\Base\AdminController;
use Common\Plugin\Title;

/**
 * 头衔管理类
 * @package Backend\Controller\Member
 */
class TitleController extends AdminController
{
    /** @var  头衔管理类 */
    protected $_title;
    /**
     * 构造方法
     */
    protected function _init()
    {
        $this->_title = new Title();
        $this->table = $this->_title->table;
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
        $type = I('type','','trim');
        if( $type ){
            $map['type'] = $type;
        }
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
                $res = $this->_title->get_title_data($map);
                $res['type_list'] = $this->_title->_type();
                $res['type_html'] = $this->get_type(I('type'));
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
            $result = $this->_title->_add();
            if(is_numeric($result)) {
                $this->success('操作成功！', U('index'));
            }
            $this->error($result, U('index'));
        }
        $data['type'] = $this->get_type();
        $this->assign($data);
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
        $data['info'] = $this->_title->_info(array('id'=>$id));
        if(!$data['info']) {
            $this->error('信息不存在');
        }
        if(IS_POST) {
            $result = $this->_title->_edit();
            if(is_numeric($result)) {
                $this->success('操作成功！', U('index'));
            }
            $this->error($result, U('index'));
        }
        $data['type'] = $this->get_type( $data['info']['type'] );
        $this->assign($data);
        $this->display('operate');
    }

    /**
     * 获取头衔下拉选择
     */
    protected  function get_type( $id = 0 )
    {
        $type = $this->_title->_type();
        $html = $this->html($type,$id,'type','请选择头衔类型','form-control');
        return $html;
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