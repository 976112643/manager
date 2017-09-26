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

        /** 查询指定用户的笔记*/
        $map = array(
            'uid' => I('uid'),
        );
        $res = $this->page($this->table, $map, 'updatetime asc', true);
        SUCCESS($res);
    }
    /**
     * 获取新的笔记列表
     */
    public function get_new_notes()
    {

        /** 查询指定用户及指定版本的数据*/
        $map = array(
            'uid' => I('uid'),
            'version'=>array('GT'=>I('version'))
        );
        $res = $this->page($this->table, $map, 'updatetime asc', true);
        SUCCESS($res);
    }

    public function update_all(){
        if (IS_POST) {
            $this->write_api_log();
            $post = I('post.');
            $rule = array(
                array('content', 'require', '请输入内容'),
                array('id', 'require', '请输入内容'),
            );
            $map = array(
                'id' => I('id'),
                'uid' => I('uid'),
            );
            $info = get_info($this->table, $map, true);
            if (!$post['addtime']) $post['addtime'] = millisecond();
            if (!$post['updatetime']) $post['updatetime'] = millisecond();
            $post['version']=intval($info['version']+1);//版本+1
            $res = update_data($this->table, $rule, [], $post);
            if (is_numeric($res)) {
                SUCCESS($post['version'], '修改成功');
            }
            ERROR($res);
        }
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
            $map = array(
                'id' => I('id'),
                'uid' => I('uid'),
            );
            $info = get_info($this->table, $map, true);
            if (!$post['addtime']) $post['addtime'] = millisecond();
            if (!$post['updatetime']) $post['updatetime'] = millisecond();
            $post['version']=$info['version']+1;//版本+1
            $res = update_data($this->table, $rule, [], $post);
            if (is_numeric($res)) {
                SUCCESS($post['version'], '修改成功');
            }
            ERROR($res);
        } else {
            $map = array(
                'id' => I('id'),
                'uid' => I('uid'),
            );
            $info = get_info($this->table, $map, true);
            if ($info) {
                SUCCESS($info);
            } else {
                ERROR('笔记获取失败');
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
    public function del()
    {
        $post = I('post.');
        $rst = delete_data($this->table, array('id' => $post['id']));
        if ($rst) {
            SUCCESS($rst, '删除成功');
        }
        ERROR('操作失败');
    }
}

?>