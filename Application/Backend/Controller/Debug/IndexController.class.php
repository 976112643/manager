<?php
namespace Backend\Controller\Debug;

use Backend\Controller\Base\AdminController;
use Common\Help\BaseHelp;
/**
 * 调试类
 */
class IndexController extends AdminController
{
    /** 操作表名*/
    protected $table = 'debug';

    /**
     * 列表
     */
    public function index()
    {
        $key = I('keywords', '', 'trim');
        $map = array();
        if ($key) {
            $map = array(
                'item' => array(
                    'like',
                    '%' . I('keywords', '', 'trim') . '%'
                )
            );
        }
        
        $this->page($this->table, $map, 'id desc', array(), 30);
        $this->display('Debug/Index/index');
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
        $info = get_info($this->table, $map);
        $info['content'] = json_decode($info['content'], true);
        $this->assign('info', $info);
        $this->display('operate');
    }
}

?>