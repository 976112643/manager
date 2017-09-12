<?php
namespace Backend\Controller\Admin;

/**
 * 后台用户管理
 */
class AdminController extends IndexController
{

    /**
     * 表名
     * 
     * @var string
     */
    protected $table = 'admin';

    /**
     * 列表
     * 
     * @author 秦晓武
     *         @time 2016-05-31
     */
    function index()
    {
        $map = array();
        $all_role = get_no_del('admin_role');
        $all_child = array_keys($all_role);
        $member_info = session('member_info');
        $map['role_id'] = array(
            'neq',
            0
        );
        if ($member_info['role_id']) {
            $all_child = get_all_child_ids($all_role, $member_info['role_id'], false);
            if (count($all_child)) {
                $map['role_id'] = array(
                    'in',
                    $all_child
                );
            } else {
                $map['role_id'] = array(
                    'in',
                    '-1'
                );
            }
        }
        /* 用户组 */
        if (strlen(I('role_id')) && in_array(I('role_id'), $all_child)) {
            $map['role_id'] = array(
                'in',
                array_intersect($all_child, get_all_child_ids($all_role, I('role_id')))
            );
        }
        /* 禁用 */
        if (strlen(trim(I('is_hid')))) {
            $map['is_hid'] = I('is_hid');
        }
        /* 用户名 */
        if (strlen(trim(I('username')))) {
            $map['username'] = array(
                'like',
                '%' . trim(I('username')) . '%'
            );
        }
        /* 姓名 */
        if (strlen(trim(I('nickname')))) {
            $map['nickname'] = array(
                'like',
                '%' . trim(I('nickname')) . '%'
            );
        }
        /* 邮箱 */
        if (strlen(trim(I('email')))) {
            $map['email'] = array(
                'like',
                '%' . trim(I('email')) . '%'
            );
        }
        
        $result = $this->page($this->table, $map, 'sort desc');
        array_walk($result['list'], function (&$item) use ($all_role, $member_info) {
            $item['role_text'] = array_to_crumbs($all_role, $item['role_id'], array(
                'root' => $member_info['role_id']
            ));
        });
        
        $result['role'] = array_to_select($all_role, I('role_id'), array(
            'root' => $member_info['role_id']
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
        $info['role_select'] = array_to_select(get_no_del('admin_role'), $info['role_id'], array(
            'root' => $member_info['role_id']
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
        /* 获取前台传递的添加参数 */
        $data = array(
            'id' => trim(I('id')),
            'username' => trim(I('username')),
            'password' => I('password'),
            'password_check' => I('password'),
            'role_id' => trim(I('role_id')),
            'email' => trim(I('email')),
            'nickname' => trim(I('nickname'))
        );
        /* 密码处理 */
        if ($data['password']) {
            $salt = get_rand_char(6);
            $data['salt'] = $salt;
            $data['password'] = md5(md5($data['password']) . $salt);
        } else {
            unset($data['password']);
        }
        /* 验证参数 */
        $rules = array();
        $rules[] = array(
            'username',
            '6,20',
            '用户名为6-20位',
            1,
            'length'
        );
        $rules[] = array(
            'username',
            '',
            '用户名已存在',
            1,
            'unique'
        );
        $rules[] = array(
            'password_check',
            NUM_CHAR_SPECIAL,
            '8-30位密码（必须包含数字字母和特殊字符）',
            1,
            '',
            1
        );
        $rules[] = array(
            'password_check',
            NUM_CHAR_SPECIAL,
            '8-30位密码（必须包含数字字母和特殊字符）',
            2,
            '',
            2
        );
        $rules[] = array(
            'role_id',
            'require',
            '用户组必须填',
            1
        );
        $rules[] = array(
            'email',
            EMAIL,
            '邮箱格式错误',
            2
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
    function access()
    {
        if (IS_POST) {
            $id = intval(I('id'));
            $access_id_arr = I('post.access_id');
            $rules = $access_id_arr ? implode(',', $access_id_arr) : '';
            
            $post['id'] = $id;
            $post['rules'] = $rules;
            $result = update_data($this->table, '', '', $post);
            if (is_numeric($result)) {
                session("menu_result", null);
                $this->success('操作成功', U('index'));
            } else {
                $this->error($result);
            }
        } else {
            $menus = get_result('menu', '', 'sort desc');
            $data['menus'] = list_to_tree($menus);
            $data['info'] = get_info('admin', array(
                'id' => intval(I('ids'))
            ));
            $roles = get_info('admin_role', array(
                'id' => $data['info']['role_id']
            ));
            $data['info']['rules'] .= $roles ? ',' . $roles['menu_ids'] : '';
            $this->assign($data);
            $this->display();
        }
    }
}

