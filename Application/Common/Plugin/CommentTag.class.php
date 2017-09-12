<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/9
 * Time: 16:25
 */
namespace Common\Plugin;

/**
 * 评论标签管理
 */
class CommentTag extends Base
{
    protected function __init()
    {
        $this->config['form'] = array(
            array('func'=> 'name', 'field'=> 'name'),/*验证头衔名称*/
            array('func'=> 'sort', 'field'=> 'sort'),/*验证排序*/
        );
        $this->table = 'order_comment_tag';
    }
    /**
     * 表单内容验证（总函数）
     */
    protected function _form() {
        foreach($this->config['form'] as $key=> $value) {
            call_user_func(array($this, 'check_'.$value['func']), $value['field']);
        }
    }
    /*
	 * 名称验证
	 */
    public function check_name($field) {
        $this->rule[] = array($field, 'require', '标签名称不能为空');
        $this->rule[] = array($field, '2,10', '标签名称超出字数为2~10', 0, 'length');
        $this->form[$field] = I('post.'.$field, '');
    }
    /**
     * 排序
     */
    public function check_sort($field) {
        $this->rule[] = array($field,'0,9999','排序不能超出9999',0,'between');
        $this->form[$field] = I('post.'.$field, 0, 'int');
    }
    /**
     * ID
     */
    public function check_id($field)
    {
        $this->form[$field] = I('post.'.$field, 0, 'int');
    }
    /**
     * 添加数据
     */
    public function _add(){
        try {
            $M=M();
            $M->startTrans();
            $this->_form();
            $res=update_data($this->table, $this->rule, array(), $this->form);
            if(!is_numeric($res)){
                throw new \Exception($res, 1);
            }
            /** 清除F缓存 */
            $this->clear_table_cache();
        } catch (\Exception $e) {
            $M->rollback();
            return $e->getMessage();
        }
        $M->commit();
        return $res;
    }
    /*
     * 编辑
     */
    public function _edit($map = array()) {
        try {
            $M = M();
            $M->startTrans();
            $config = array(
                array('func'=>'id','field'=>'id'),
            );
            $this->config['form'] = array_merge($this->config['form'],$config);
            $this->_form();
            $result = update_data($this->table, $this->rule, $map, $this->form);
            if(!is_numeric($result)) {
                throw new \Exception($result);
            }
            /** 清除F缓存 */
            $this->clear_table_cache();
        } catch (\Exception $e) {
            $M->rollback();
            return $e->getMessage();
        }
        $M->commit();
        return $result;
    }

    /**
     * 后台获取标签列表
     * @param $map
     * @return mixed
     */
    public function get_title_data( $map )
    {
        return $this->page($this->table,$map);
    }
    /**
     * 详情
     */
    public function _info($map = array())
    {
        return get_info($this->table,$map);
    }
    /**
     * 根据标签ID获取标签内容
     */
    public function get_data_by_id( $id = '0' )
    {
        $data = get_no_del($this->table);
        return $id ? $data[$id] : $data;
    }
    /**
     * 根据标签ID获取标签内容
     */
    public function get_data_by_ids( $ids = array() )
    {
        if( count($ids) < 1 ) return '';
        $data = get_no_del($this->table);
        $arr = array();
        foreach ($ids as $key => $value) {
            $tag = $data[$value];
            $arr[$key] = array(
                'id' => $tag['id'],
                'name' =>$tag['name']
            );
        }
        return $arr;
    }
}