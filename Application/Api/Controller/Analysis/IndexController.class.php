<?php
namespace Api\Controller\Analysis;

use Api\Controller\Base\BaseController;

/**
 * 统计分析控制器
 * @author Administrator
 */
class IndexController extends BaseController
{
    /** 内容表*/
    protected $table = 'analysis';


	 /**
     * 统计访问次数
	 * hash_info 统计项唯一标识
	 * type 类型 int 默认0 1是url
	 * title 统计标题
	 * desc 描述
      * url 地址 统计页面的话可以带着
      *
     */
	public function accum_analysis(){
			$map = I('get.');
			if($map['hash_info']){
				$map['hash_info']=md5($map['hash_info']);
			}
			$rule = array(
                array('hash_info', 'require', 'hash_info必须'),
            );
            $info = get_info($this->table, $map, true);
			
            if ($info) {
				$info['count']=$info['count']+1;
				//var_dump($info);
                $res = update_data($this->table, $rule, [], $info);
				if (is_numeric($res)) {
					SUCCESS($info['count'], 'success');
				}
				ERROR($res);
            } else {
				$analy_info=$map;
				$analy_info['count']=1;
				//var_dump($analy_info);
				$res = update_data($this->table, $rule, [], $analy_info);
                if (is_numeric($res)) {
					SUCCESS($analy_info['count'], 'success');
				}
				ERROR($res);
            }
	}
	
	public function analysis_list(){
		
	}
	
 
    public function index()
    {

      
      ERROR("呃....你想干嘛");
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
//        unset($map);
//        SUCCESS($map);
        $res = $this->page($this->table, $map, 'updatetime asc', true);
        SUCCESS($res);
    }

    public function update_all()
    {
        if (IS_POST) {
            $this->write_api_log();
            $post = $_POST;
            $notes = json_decode($post['notes'], true);
            $ids="";
            foreach ($notes as $note) {
                $map = array(
                    'note_id' => $note['note_id'],
                    'uid' => I('uid'),
                );
                if($note['id']==0)unset($note['id']);
                $info = get_info($this->table, $map, true);
                //SUCCESS($info);
                $note['uid']=I('uid');
                if(!$info)$map=[];
                if (!$note['addtime']) $note['addtime'] = millisecond();
                if (!$note['updatetime']) $note['updatetime'] = millisecond();
                $note['version']=$info['version']+1;//版本+1
                $res = update_data($this->table, [], $map, $note);
                $ids=$ids.$res.',';
                if (!is_numeric($res)) {
                    ERROR($res);
                }
            }
            $ids=substr($ids,0,strlen($ids)-1);
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