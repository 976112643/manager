<?php
namespace Api\Controller\Note;

use Api\Controller\Base\BaseController;

/**
 * 笔记控制器
 * @author Administrator
 */
class IndexController extends BaseController
{
    /** 内容表*/
    protected $table = 'note';
    protected $note_type = array('TEXT');

    /**
     * 笔记列表
     */
    public function index()
    {

        /** 读取缓存*/
        $cache = $this->table . '_page_' . I('p', 1) . I('r', 10);
       // if (F($cache)) {
      //      $res = F($cache);
     //   } else {
            //$field = 'id,article_title,article_headimg,article_author,article_content,article_publish_time,article_img';
            $map = array(
                'uid'=>I('uid'),
            );
            $res = $this->page($this->table, $map, 'updatetime asc', true);
            F($cache, $res);
     //   }
        SUCCESS($res);
    }

    /**
     * 笔记获取/修改
     */
    public function edit()
    {
        if (IS_POST) {
            $post = I('post.');
            $rule = array(
                array('content', 'require', '请输入内容'),
                array('id', 'require', '请输入内容'),
            );
            if (!$post['addtime']) $post['addtime'] = millisecond();
            $res = update_data($this->table, $rule, [], $post);
            if (is_numeric($res)) {
                SUCCESS($res, '修改成功');
            }
            ERROR($res);
        } else {
            $map = array(
                'id' => I('id'),
                'uid'=>I('uid'),
                );
            $info = get_info($this->table, $map, true);
            if($info){
                SUCCESS($info);
            }else{
                ERROR('笔记获取失败');
            }
        }
    }

    /**
     * 添加笔记
     */
    public function add()
    {
        $member_info = $this->get_memberinfo(false);
        //echo ''.millisecond();
        $post = I('post.');
        $rule = array(
            array('content', 'require', '请输入内容'),
        );
        if (!$post['addtime']) $post['addtime'] = millisecond();
        if (!$post['updatetime']) $post['updatetime'] = millisecond();
        if (!$post['type']) $post['type'] = $this->note_type[0];
        $res = update_data($this->table, $rule, [], $post);
        if (is_numeric($res)) {
            SUCCESS($res, '添加成功');
        }
        ERROR($res);
    }

    /**
     * 删除笔记
     */
    public function del(){
        $post = I('post.');
        $rst=delete_data($this->table,array('id'=>$post['id']));
        if($rst){
            SUCCESS($rst,'删除成功');
        }
        ERROR('操作失败');
    }
}

?>