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
    protected $table = 'note';

    /**
     * 列表
     *
     */
    public function index()
    {
        $status=I('status');
        $map = $this->default_map('content','addtime',true,true);
        $map['status'] = $status?$status:'0';

        $this->page($this->table, $map, 'addtime desc', array(), 30);
        $this->display('Article/Index/index');
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
        $this->assign('info', $info);
        $this->display('operate');
    }
}

?>