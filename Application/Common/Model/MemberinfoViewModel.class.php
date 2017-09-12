<?php 
namespace Common\Model;

use Think\Model\ViewModel;
/**
 * 会员表连接 会员信息表的视图
 * @author  鲍海
 * @time    2017.03.14
 */
class MemberinfoViewModel extends ViewModel{
	/**
     * 模型字段定义
     * @var array
     */
    public $viewFields = array(
		'member'=> array(
			'*',
			'_type' => 'left' ,
		),
		'member_info'=> array(
			'uid',
			'mobile',
			'nickname',
			'head_img',
			'signature',
			'gender',
			'age',
			'constellation',
			'integral',
			'skill',
			'hobbies',
			'title_id',
			'sum_task_num',
			'sum_money_num',
			'start_rating',
			'birthday',
			'_on' => 'member_info.uid = member.id' ,
		),
	);
}

?>