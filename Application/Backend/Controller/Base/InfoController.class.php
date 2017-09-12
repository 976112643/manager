<?php
namespace Backend\Controller\Base;

/**
 * 个人信息管理
 */
class InfoController extends IndexController
{

    /**
     * 表名
     * 
     * @var string
     */
    protected $table = 'admin';

    /**
     * 详情页
     * 
     * @author 秦晓武
     *         @time 2016-05-31
     */
    function index()
    {}

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
            'id' => session('member_id')
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
        $data = array(
            'id' => session('member_id'),
            'new_password' => I('new_password'),
            'new_password_check' => I('new_password_check'),
            'email' => trim(I('email')),
            'nickname' => trim(I('nickname'))
        );
        $rules = array();
        /* 获取前台传递的添加参数 */
        if (I('old_password') || I('new_password') || I('new_password_check')) {
            $member_info = get_info($this->table, array(
                'id' => session('member_id')
            ));
            if (md5(md5(I('old_password')) . $member_info['salt']) != $member_info['password']) {
                $this->error('原密码错误');
            }
            $salt = get_rand_char(6);
            $data['salt'] = $salt;
            $data['password'] = md5(md5(I('new_password')) . $salt);
            $rules[] = array(
                'new_password',
                NUM_CHAR_SPECIAL,
                '8-30位密码（必须包含数字字母和特殊字符）',
                2
            );
            $rules[] = array(
                'new_password_check',
                'new_password',
                '确认密码不正确',
                1,
                'confirm'
            );
        }
        /* 验证参数 */
        $rules[] = array(
            'email',
            EMAIL,
            '邮箱格式错误',
            2
        );
        $result = update_data($this->table, $rules, $map, $data);
        if (is_numeric($result)) {
            $this->success('操作成功', U('edit'));
        } else {
            $this->error($result);
        }
    }
}

