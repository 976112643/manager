<?php
namespace Backend\Controller\Debug;

/**
* 接口调试记录
*/
class ApiController extends IndexController
{
	/**
	 * 表名  API统计表
	 * @var string
	 */
	protected $table = 'api_count';
	/**
     * 列表
     */
    public function index()
    {
        $map = $this->default_map('group');

        $this->page($this->table, $map, 'id desc', array(), 30);
        $this->display();
    }
}
?>