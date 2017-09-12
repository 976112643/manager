<?php 
namespace Api\Controller\Feekback;

use Api\Controller\Base\AuthController;

/**
 * 【通用】意见反馈
 * @author Administrator
 */
class IndexController extends AuthController
{
    /** 意见反馈表*/
    protected $table = 'feekback';
    /** 意见反馈类型*/
    protected $type = array(
        array('id'=>'10','title' =>'违规行为投诉'),
        array('id'=>'20','title' =>'侵权投诉（人身权，知识产权等被侵犯）'),
        array('id'=>'30','title' =>'功能使用意见或建议反馈'),
        array('id'=>'40','title' =>'其他'),
    );
    /**
     * 获取意见反馈类型
     */
    public function get_type()
    {
        $this->set_success('ok',$this->type);
    }
    /**
     * 添加反馈意见
     * 1、验证数据是否合法
     * 2、验证是否重复提交
     */
    public function add()
    {
        $posts = I('post.','','trim');
        /** 防止恶意提交 */
        $cache = 'feekback:'.$this->_id;
        $flag = $this->check_add($this->_id,$cache);
        if($flag){
            $this->set_error('请不要重复提交,5秒后再尝试');
        }

        $data = array(
            'contact' =>$posts['contact'],
            'content' =>$posts['content'],
            'member_id' =>$this->_id,
        );
        $rule = array(
            array('content','require','请输入反馈内容'),
            array('content','1,255','请输入1-255位的反馈内容',0,'length'),
            array('member_id','require','用户ID错误'),
            array('contact','require','请输入联系方式'),
            array('contact',MOBILE,'手机号格式错误'),
        );
        $res = update_data($this->table,$rule,[],$data);
        if(is_numeric($res)){
            $this->set_success('意见反馈成功',array($res));
        }else{
            $this->set_error($res);
        }
    }
}


?>