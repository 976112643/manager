<?php 
namespace Backend\Controller\Message;

use Backend\Controller\Base\AdminController;
/**
 * 消息中心-意见反馈
 * @author Administrator
 */
class FeekbackController extends AdminController
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
     * 列表
     */
    public function index()
    {
        $map = $this->get_map();
        $data = $this->page($this->table,$map,'add_time desc,id desc');
        if($data['list']){
            $ids = array_column($data['list'], 'member_id');
            $member_list = get_result('member_info',array('uid'=>array('in',$ids)),'id asc','uid,nickname');
            $list = array_column($member_list, null,'uid');
            $all_type = array_column($this->type, null,'id');
            array_walk($data['list'], function(&$a) use($list,$all_type){
                $a['nickname'] = $list[$a['member_id']]['nickname'];
                $a['type_text'] = $all_type[$a['type']]['title'];
            });
        }
        $data['type'] = $this->type;
        $this->assign($data);
        $this->display();
    }
    /**
     * 详情
     */
    public function details()
    {
        $ids = I('ids');
        $info = get_info($this->table,array('id'=>$ids));
        if($info){
            $member_nickname = M('member')
                                ->where(array('id'=>$info['member_id']))
                                ->getField('nickname');
            $info['nickname'] = $member_nickname;
            $this->assign('info',$info);
        }
        $this->display('operate');
    }
    
    /**
     * 列表搜索条件
     */
    private function get_map()
    {
        $map = $this->default_map('content');
        /** @var $type [反馈类型] */
        $type = I('type','','trim');
        if($type){
            $map['type'] = $type;
        }

        return $map;
    }
}


?>