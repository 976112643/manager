<?php
/**
 * 敏感词管理
 * @package 
 * @author 陶君行 
 */
namespace Backend\Controller\System;
use Common\Plugin\Sensitive;
use Common\Help\StrHelp;
/**
 * 敏感词管理
 * 
 * @package
 *
 * @author 陶君行
 */
class SensitiveController extends IndexController
{
    protected $table = 'sensitive';
    /**
     * 功能：查询数据库下的所有表
     * 
     * @author 陶君行
     */
    public function index()
    {
        $keyword = I('get.title', '', '/\S+/');
        if (! empty($keyword)) {
            $map['name'] = array(
                'like',
                trim($keyword)
            );
        }
        $result = $this->page($this->table,$map,'sort desc,id asc',true,'15');
        
        /* 加载页面 */
        $this->display();
    }
    /**
     * 导入敏感词
     */
    public function import()
    {
        if(IS_POST){
            $post = I('post.');
            if(!$post['save_path']) $this->error('请上传文件');
            $file = get_info('file',array('id'=>$post['save_path']));
            /** 获取敏感词文件中的内容 */
            $sensitive = new Sensitive($file['save_path']);
            $arr = $sensitive->getFileContentByLine();
            $_data = array();
            foreach ($arr as $key => $value) {
                $a = array();
                $a['name'] = $value;
                $_data[] = $a;
            }
            $sql = addSql($_data,$this->table);
            $key = execute_sql($sql);
            if($key>0){
                /** 清理缓存 */
                $this->clean_cache();
                $this->success('导入成功');
            }else{
                $this->error('导入失败');
            }
        }else{
            $this->display('import');
        }
    }
    /**
     * 去重敏感词
     * @author 陶君行
     */
    public function remove_more()
    {
        /** 查找所有重复的数据 */
        $sql = 'select id,name,count(*) as count from sr_sensitive group by name having count>1';
        $data = query_sql($sql);
        if(!$data ) $this->error('暂无重复数据');
        /** 取出所有的ID，名字 */
        $ids = array_column($data,'id');
        $names = array_column($data,'name');

        /** 更新那些name 在names 中,id 不在ids 中的数据 */
        $map = array(
            'name' =>array('in',$names),
            'id' =>array('not in',$ids)
        );
        $res = update_data($this->table,[],$map,array('is_del'=>'1'));
        /** 是否删除这些数据 */
        delete_data($this->table,array('is_del'=>'1'));
        $this->success('已去除'.count($ids).'条数据');
    }
    /**
     * 添加操作
     * @time 2014-12-26
     * 
     * @author 郭文龙 <2824682114@qq.com>
     */
    public function add()
    {
        if (IS_POST) {
            $this->update();
        } else {
            $this->display('operate');
        }
    }

    /**
     * 修改操作
     * @time 2014-12-26
     * 
     * @author 郭文龙 <2824682114@qq.com>
     */
    public function edit()
    {
        if (IS_POST) {
            $this->update();
        } else {
            $id = intval(I('ids'));
            $map['id'] = $id;
            $data['info'] = M($this->table)->where($map)->find();
            $this->assign($data);
            $this->display('operate');
        }
    }

    /**
     * 添加/修改操作
     * @time 2014-12-26
     * 
     * @author 康利民 <3027788306@qq.com>
     */
    public function update()
    {
        if (IS_POST) {
            $id = intval(I('post.id'));
            $rules = array(
                array(
                    'name',
                    'require',
                    '请填写词语'
                ),
                array(
                    'name',
                    '1,50',
                    '词语长度不能超过50个字',
                    '0',
                    'length'
                )
            );
            $result = update_data($this->table, $rules);
            if (is_numeric($result)) {
                $this->success('操作成功', U('index'));
            } else {
                $this->error($result);
            }
        } else {
            $this->success('违法操作', U('index'));
        }
    }
}
