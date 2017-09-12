<?php
namespace Backend\Controller\Admin;

/**
 * 后台用户组管理
 * @time 2016-08-02
 * 
 * @author 秦晓武
 */
class RoleController extends IndexController
{

    /**
     * 表名
     * 
     * @var string
     */
    protected $table = 'admin_role';

    /**
     * 列表
     * 
     * @author 秦晓武
     *         @time 2016-05-31
     */
    function index()
    {
        $all_role = get_no_del($this->table);
        $all_child = array_keys($all_role);
        $map = array();
        $member_info = session('member_info');
        $pid = $member_info['role_id'];
        if ($pid) {
            $all_child = get_all_child_ids($all_role, $pid, false);
            if (count($all_child)) {
                $map['id'] = array(
                    'in',
                    $all_child
                );
            } else {
                $map['id'] = array(
                    'in',
                    '-1'
                );
            }
        }
        /* 分类 */
        if (strlen(I('pid')) && in_array(I('pid'), $all_child)) {
            $map['id'] = array(
                'in',
                array_intersect($all_child, get_all_child_ids($all_role, I('pid')))
            );
        }
        /* 数据少无需分页(和筛选不兼容) */
        $no_page = true;
        if ($no_page) {
            $result['list'] = get_result($this->table, $map, 'sort desc');
            $tree = array_to_tree($result['list'], array(
                'root' => $pid,
                'function' => array(
                    'format_data' => function ($row, $set) {
                        /* 遍历调整显示结构 */
                        for ($i = $set['level']; $i --; $i == 0) {
                            $row['title'] = '|--' . $row['title'];
                        }
                        return $row;
                    }
                )
            ));
            $result['list'] = tree_to_array($tree);
        }
        $result['pid'] = array_to_select($all_role, I('pid'), array(
            'root' => $pid
        ));
        $this->assign($result);
        $this->display();
    }

    /**
     * 添加
     * 
     * @author 秦晓武
     *         @time 2016-05-31
     */
    public function add()
    {
        if (IS_POST) {
            $this->update();
        } else {
            $this->operate();
        }
    }

    /**
     * 编辑
     * 
     * @author 秦晓武
     *         @time 2016-05-31
     */
    public function edit()
    {
        if (IS_POST) {
            $this->update();
        } else {
            $this->operate();
        }
    }

    /**
     * 显示
     * 
     * @author 秦晓武
     *         @time 2016-05-31
     */
    protected function operate()
    {
        $info = get_info($this->table, array(
            'id' => I('ids')
        ));
        $member_info = session('member_info');
        $info['pid'] = array_to_select(get_no_del($this->table), $info['pid'], array(
            'root' => $member_info['role_id'],
            'root_show' => true
        ));
        $data['info'] = $info;
        $this->assign($data);
        $this->display('operate');
    }

    /**
     * 修改
     * 
     * @author 秦晓武
     *         @time 2016-05-31
     */
    protected function update()
    {
        $data = I('post.');
        /* 获取前台传递的添加参数 */
        $data['user_id'] = session('member_id');
        /* 验证参数 */
        $rules[] = array(
            'title',
            'require',
            '标题必须填',
            1
        );
        $rules[] = array(
            'title',
            '',
            '用户组已存在',
            0,
            'unique',
            1
        );
        $result = update_data($this->table, $rules, $map, $data);
        if (is_numeric($result)) {
            $this->success('操作成功', U('index'));
        } else {
            $this->error($result);
        }
    }

    /**
     * 访问授权
     * @time 2016-07-28
     * 
     * @author 秦晓武
     */
    public function access()
    {
        if (IS_POST) {
            $id = intval(I('id'));
            $access_id_arr = I('post.access_id');
            $rules = $access_id_arr ? implode(',', $access_id_arr) : '';
            
            $post['id'] = $id;
            $post['menu_ids'] = $rules;
            $result = update_data($this->table, '', '', $post);
            if (is_numeric($result)) {
                session("menu_result", null);
                action_log($id);
                $this->success('操作成功', U('index'));
            } else {
                $this->error($result);
            }
        } else {
            $data['info'] = get_info($this->table, array(
                'id' => intval(I('ids'))
            ));
            $map = array();
            /* 继承父级权限 */
            if ($data['info']['pid']) {
                $parent = get_info($this->table, array(
                    'id' => $data['info']['pid']
                ));
                $map['id'] = $parent['menu_ids'] ? array(
                    'in',
                    $parent['menu_ids']
                ) : 0;
            }
            $menus = get_result('menu', $map, 'sort desc');
            $data['menus'] = list_to_tree($menus);
            $this->assign($data);
            $this->display();
        }
    }
}
