<?php
namespace Api\Controller\Index;

use Api\Controller\Base\BaseController;

/**
 * 默认首页空控制器
 * @author Administrator
 */
class IndexController extends BaseController
{
    /** 广告表*/
    protected $table = 'content';

    public function index()
    {

        /** 读取缓存*/
        $cache = 'content_'.I('p',1);
        if (F($cache)) {
            $res = F($cache);
        } else {

            $field = 'id,depth,url,article_title,article_headimg,article_author,article_content,article_publish_time,article_img';
            $res = $this->page($this->table, null, 'id asc', $field);
            F($cache, $res);
        }
        $this->set_success("请求成功",$res);
    }
}

?>