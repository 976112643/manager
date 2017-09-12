<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/9
 * Time: 16:31
 */
namespace Common\Plugin;
use Common\Controller\CommonController;

class Base extends CommonController
{
    public $table;
    public $config;
    public $form;
    public $rule;
    /**
     * 初始化
     */
    protected function __autoload()
    {
        $this->__init();
    }
    /**
     * 子类继承
     */
    protected function __init(){}

    protected function clear_table_cache( $table = '' )
    {
        $table = $table ? $table : $this->table;
        F($table.'_no_hid',null);
        F($table.'_no_del',null);
    }
}
?>