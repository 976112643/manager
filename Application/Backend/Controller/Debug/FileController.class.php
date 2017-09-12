<?php
namespace Backend\Controller\Debug;
use Backend\Controller\Base\AdminController;
/**
 * 日志文件记录
 */
class FileController extends AdminController
{
    /**
     * 列表
     */
    public function index()
    {
        $this->display();
    }
}

?>