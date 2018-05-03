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
      * read_only 1
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
                if($map['read_only']){
                    SUCCESS($info['count'], 'success');
                }
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

        /** 查询指定用户的笔记*/
        $map = array(
            'type' => I('type'),
        );
        $res = $this->page($this->table, $map, 'update_time asc', true);
        SUCCESS($res);
	}
	
 
    public function index()
    {

      
      ERROR("呃....你想干嘛");
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