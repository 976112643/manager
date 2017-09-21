<?php
namespace Api\Controller\Note;

use Api\Controller\Base\BaseController;

/**
 * 默认首页空控制器
 * @author Administrator
 */
class IndexController extends BaseController
{
    /** 内容表*/
    protected $table = 'note';

    public function index()
    {

        /** 读取缓存*/
        $cache = $this->table.'_page_'.I('p',1).I('r',10);
        if (F($cache)) {
            $res = F($cache);
        } else {
            //$field = 'id,article_title,article_headimg,article_author,article_content,article_publish_time,article_img';
            $res = $this->page($this->table, null, 'updatetime asc', true);
            F($cache, $res);
        }
		SUCCESS($res);
    }
	
	public function add(){
		
	}
}

?>