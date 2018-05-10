<?php
namespace Api\Controller\Note;

use Api\Controller\Base\BaseController;

/**
 * 笔记分类控制器
 * @author Administrator
 */
class CategoryController extends BaseController
{
    /** 内容表*/
    protected $table = 'note_category';

    /**
     * 笔记列表
     */
    public function index()
    {

        /** 查询指定用户的笔记*/
        $map = array(
//            'uid' => I('uid'),
        );
        $res = $this->page($this->table, $map, 'updatetime asc', array('id','title','addtime','sort','num'));
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
                array('title', 'require', '请输入名称'),
                array('id', 'require', '请输入内容'),
            );
            $map = array(
                'id' => I('id'),
                'uid'=>I('uid')
            );
            $info = get_info($this->table, $map, true);
            if (!$post['addtime']) $post['addtime'] = millisecond();
            if (!$post['updatetime']) $post['updatetime'] = millisecond();
            $res = update_data($this->table, $rule, [], $post);
            if (is_numeric($res)) {
                SUCCESS($post['version'], '修改成功');
            }
            ERROR($res);
        } else {
            $map = array(
                'id' => I('id'),
                'uid'=>I('uid')
            );
            $info = get_info($this->table, $map, true);
            if ($info) {
                SUCCESS($info);
            } else {
                ERROR('获取失败');
            }
        }
    }

    /**
     * 添加笔记
     * uid 用户id
     * content 笔记内容 Y
     * id 笔记id
     * type 笔记类型
     * addtime 笔记时间
     * updatetime 更新时间
     */
    public function add()
    {
        $member_info = $this->get_memberinfo(false);
        //echo ''.millisecond();
        $post = I('post.');
        if ($post['id']) {//存在id则直接调取编辑
            $this->edit();
        }
        $rule = array(
            array('title', 'require', '请输入标题'),
            array('uid', 'require', '用户信息必须'),
        );
        if (!$post['addtime']) $post['addtime'] = millisecond();
        if (!$post['updatetime']) $post['updatetime'] = millisecond();
        $res = update_data($this->table, $rule, [], $post);
        if (is_numeric($res)) {
            SUCCESS($res, '添加成功');
        }
        ERROR($res);
    }


    /**
     * 删除笔记
     */
    public function del()
    {
        $post = I('post.');
        $ids=$post['ids'];
        $map = array(
            'uid' => I('uid'),
            'id' => array(
            'in',
            $ids
        ));
        $rst = delete_data($this->table, $map);
        if ($rst) {
            SUCCESS($rst, '删除成功');
        }
        ERROR('操作失败');
    }
}

?>