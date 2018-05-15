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
    protected $note_model='NoteDetailsView';
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
            'version' => array('GT', I('version'))
        );
        $res = $this->page($this->table, $map, 'updatetime asc', true);
        SUCCESS($res);
    }

    /**
     * 获取id及对应版本号小于服务器的数据
     * http://localhost/Api/Note/Index/get_diff_notes?uid=15&ids[0]=97&ids[1]=98&versions[0]=5&versions[1]=10
     * ids id数组 versions版本数组 ,长度和顺序需要对应
     */
    public function get_diff_notes()
    {

        /** 查询指定用户及指定版本的数据*/
        $versions=I('versions');
        $ids=I('ids');
        if(!$versions||!$ids||count($versions)!=count($ids)){
            ERROR('参数错误');
        }
        $map = array(
            'uid' => I('uid'),
            array('id' => array(
                'in',
                I('ids')
            ))
        );

		
        $res = $this->page($this->table, $map, 'id asc ', true);

        $result=array();
        for ($x=0; $x< count($res); $x++) {
			//echo '[  '.$res[$x]['version'].'   '.$res[$x]['id'].'  ]';
            if($res[$x]['version']>$versions[$x]){
                array_push($result,$res[$x]);
            }
        }
        SUCCESS($result);
    }

    public function update_all()
    {
        if (IS_POST) {
            $this->write_api_log();
            $post = $_POST;
            $notes = json_decode($post['notes'], true);
            $ids = "";
            foreach ($notes as $note) {
                $map = array(
                    'note_id' => $note['note_id'],
                    'uid' => I('uid'),
                );
                if ($note['id'] == 0) unset($note['id']);
                $info = get_info($this->table, $map, true);
                //SUCCESS($info);
                $note['uid'] = I('uid');
                if (!$info) $map = [];
                if (!$note['addtime']) $note['addtime'] = millisecond();
                if (!$note['updatetime']) $note['updatetime'] = millisecond();
                $note['version'] = $info['version'] + 1;//版本+1
                $res = update_data($this->table, [], $map, $note);
                $ids = $ids . $res . ',';
                if (!is_numeric($res)) {
                    ERROR($res);
                }
            }
            $ids = substr($ids, 0, strlen($ids) - 1);
            SUCCESS($ids, '修改成功');
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
            $post['version'] = $info['version'] + 1;//版本+1
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
     * 获取新的笔记列表
     */
    public function test_notes()
    {

        /** 查询指定用户及指定版本的数据*/
        $map = array('uid' => 15);
        $res = $this->page(D($this->note_model), $map, 'updatetime asc', true);
        SUCCESS($res);
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