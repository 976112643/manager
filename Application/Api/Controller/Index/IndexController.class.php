<?php
namespace Api\Controller\Index;

use Api\Controller\Base\BaseController;

/**
 * 默认首页空控制器
 * @author Administrator
 */
class IndexController extends BaseController
{
    /** 内容表*/
    protected $table = 'content';

    public function index()
    {

        /** 读取缓存*/
        $cache = 'content_page_'.I('p',1).I('r',10);
        if (F($cache)) {
            $res = F($cache);
        } else {
            $field = 'id,article_title,article_headimg,article_author,article_content,article_publish_time,article_img';
            $res = $this->page($this->table, null, 'id asc', $field);
            F($cache, $res);
        }
		SUCCESS($res);
    }
}

?>