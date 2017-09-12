<?php
namespace Backend\Controller\Admin;

/**
 * 显示日志，只能删除
 * @需求：
 * 1、日志显示，删除
 * @time 2016-08-02
 * 
 * @author 秦晓武
 */
class ActionLogController extends IndexController
{

    /**
     * 表名
     * 
     * @var string
     */
    protected $table = 'action_log';

    /**
     * 操作日志列表
     * @流程：
     * 1、关键字搜索条件处理
     * 2、根据条件获取日志列表
     */
    public function index()
    {
        $map = array();
        $member_info = session('member_info');
        $all_role = get_no_del('admin_role');
        if ($member_info['role_id']) {
            $all_child = array_keys($all_role);
            $roles = get_all_child_ids($all_role, $member_info['role_id'], false);
            
            $all_admin = get_no_del('admin');
            $admins = array_filter($all_admin, function ($item) use ($roles) {
                return in_array($item['role_id'], $roles);
            });
            $admins[$member_info['id']] = $member_info;
            
            $map['admin_id'] = array(
                'in',
                array_keys($admins)
            );
        }
        /**
         * 用户
         */
        if (strlen(I('admin_id'))) {
            $map['admin_id'] = array(
                'eq',
                I('admin_id')
            );
        }
        /**
         * 菜单
         */
        if (strlen(I('menu_id'))) {
            $map['menu_id'] = array(
                'in',
                get_all_child_ids(get_no_del('menu'), I('menu_id'))
            );
        }
        $data = $this->page($this->table, $map, 'id desc');
        $data['role_select'] = array_to_select($all_role, I('role_id'), array(
            'root' => $member_info['role_id']
        ));
        $data['admin_select'] = array_to_select($admins, I('admin_id'), array(
            'field' => array(
                'parent' => 'is_del'
            ),
            'show' => 'username'
        ));
        $data['menu_select'] = array_to_select(get_no_del('menu'), I('menu_id'), array(
            'limit' => 1
        ));
        $this->assign($data);
        $this->display();
    }

    /**
     * 删除日志
     * @time 2016-08-02
     * 
     * @author 秦晓武
     */
    function del()
    {
        $url = U('index');
        $ids = I('ids');
        if (empty(intval($ids)))
            return $this->error('请选择要删除的数据!');
        if (! is_array($ids))
            $ids = array(
                intval($ids)
            );
        $map['id'] = array(
            'in',
            $ids
        );
        $result = M($this->table)->where($map)->delete();
        if (! is_numeric($result)) {
            $this->error($result);
        }
        $this->success('操作成功', $url);
    }
}
	