<?php
namespace Backend\Controller\System;

/**
 * 菜单管理
 * 
 * @author 秦晓武
 *         @time 2016-06-30
 */
class MenuController extends IndexController
{

    /**
     * 表名
     * 
     * @var string
     */
    protected $table = 'menu';
    /** 表缓存*/
    protected $table_cache = 'menu_data';

    /**
     * 列表
     * 
     * @author 秦晓武
     *         @time 2016-05-31
     */
    public function index()
    {
        $map['pid'] = 0;
        /**
         * 父级
         */
        if (strlen(I('pid'))) {
            $map['pid'] = I('pid');
        }
        /**
         * 禁用
         */
        if (strlen(I('is_hid'))) {
            $map['is_hid'] = I('is_hid');
        }
        /**
         * 关键字
         */
        if (strlen(trim(I('keywords')))) {
            $map['title'] = array(
                'like',
                '%' . I('keywords') . '%'
            );
        }
        
        $result['list'] = get_result($this->table, $map, 'sort desc');
        
        /**
         * 前两级才有链接
         */
        array_walk($result['list'], function (&$item) {
            switch ($item['level']) {
                case 0:
                case 1:
                case 2:
                case 3:
                    $item['show_title'] = '<a href="' . U('index', array(
                        'pid' => $item['id'],
                        'level' => $item['level'] + 1
                    )) . '">' . $item['title'] . '</a>';
                    break;
                default:
                    $item['show_title'] = $item['title'];
            }
        });
        $result['parent'] = get_info($this->table, array(
            'id' => $map['pid']
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
        $info = get_info('menu', array(
            'id' => I('ids')
        ));
        $pid = $info['pid'] ? $info['pid'] : I('pid');
        $info['pid'] = array_to_select(get_no_del($this->table), $pid, array(
            'limit' => 2
        ));
        $data['info'] = $info;
        $this->assign($data);
        switch (I('get.level')) {
            case '1':
                $this->display('operate_1');
                break;
            case '2':
                $this->display('operate_2');
                break;
            case '3':
                $this->display('operate_3');
                break;
            default:
                $this->display('operate_0');
        }
        return true;
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
        /**
         * 获取前台传递的添加参数
         */
        $data['user_id'] = session('member_id');
        $data['level'] = 0;
        $data['path'] = '-0-';
        /**
         * 如果有父级，更新路径
         */
        if ($data['pid']) {
            $parent_info = get_info('menu', array(
                'id' => $data['pid']
            ));
            if (! $parent_info['id']) {
                return $this->error('添加失败，上级菜单没有找到');
            }
            $data['level'] = $parent_info['level'] + 1;
            $data['path'] = $parent_info['path'] . $data['pid'] . '-';
        }
        /**
         * 验证参数
         */
        $rules[] = array(
            'title',
            'require',
            '菜单名称必须！'
        );
        $rules[] = array(
            'url',
            'require',
            '链接必须填！'
        );
        $result = update_data('menu', $rules, array(), $data);
        if (! is_numeric($result)) {
            return $this->error($result);
        }
        /**
         * 如果有子级操作，批量添加
         */
        if (isset($data['child_menu'])) {
            $data['id'] = $result;
            $this->add_action($data);
        }
        
        /**
         * 如果前后父级不同，更新子级路径
         */
        if (isset($data['old_pid']) && $data['old_pid'] != $data['pid']) {
            update_data('menu', array(), array(
                'pid' => $result
            ), array(
                'path' => ($data['path'] . $result . '-')
            ));
        }
        $url = U('index', array(
            'pid' => I('pid', 0, 'intval'),
            'level' => I('level', 0, 'intval')
        ));
        if (is_numeric($result)) {
            $this->success('操作成功', $url);
        } else {
            $this->error($result, $url);
        }
    }

    /**
     * 删除数据库中的数据，如果是删除数据到回收站，不需要此函数
     * @time 2016-07-28
     * 
     * @author 秦晓武
     */
    function del()
    {
        $url = U('index', array(
            'pid' => I('pid', 0, 'intval'),
            'level' => I('level', 0, 'intval')
        ));
        $ids = I('ids');
        if (empty(intval($ids)))
            return $this->error('请选择要删除的菜单!');
        if (! is_array($ids))
            $ids = array(
                intval($ids)
            );
        $map = array();
        $map[]['id'] = array(
            'in',
            $ids
        );
        foreach ($ids as $key => $val) {
            $map[]['path'] = array(
                'like',
                '%-' . $val . '-%'
            );
        }
        $flag = trans(function () use ($map) {
            foreach ($map as $val) {
                $result[] = M('menu')->where($val)->delete();
            }
            return $result;
        });
        if ($flag) {
            $this->error($flag);
        }
        $this->call_back_change();
        $this->success('操作成功', $url);
    }

    /**
     * 快捷添加操作菜单
     * @流程
     * 1.
     * 根据父级信息生成对应子级信息
     * 2. 根据操作生成对应链接（通过get_action_data）
     * 3. 批量插入数据库
     * 
     * @param string $data
     *            父级信息
     * @return array 结果
     *         @time 2016-07-26
     * @author 秦晓武
     */
    private function add_action($data = array())
    {
        $action = array();
        $base = array(
            'pid' => $data['id'],
            'path' => $data['path'] . $data['id'] . '-',
            'level' => $data['level'] + 1,
            'url' => substr($data['url'], 0, strrpos($data['url'], '/')),
            'addtime' => time(),
            'updatetime' => time(),
            'user_id' => session('member_id')
        );
        foreach ($data['child_menu'] as $row) {
            $action[] = $this->get_action_data($base, $row);
        }
        M('menu')->addALL($action);
    }

    /**
     * 快捷生成操作菜单
     * @流程
     * 1.预定义常见操作的配置
     * 2.根据传递过来的ACTION_NAME，自动生成对应的链接数据
     * 
     * @param string $data
     *            基础信息
     * @param string $action
     *            ACTION_NAME
     * @return array 结果
     *         @time 2016-07-27
     * @author 秦晓武
     */
    private function get_action_data($data = array(), $action = '')
    {
        switch ($action) {
            case 'add':
                $data['title'] = '添加';
                $data['class'] = ' btn-primary ';
                $data['display_position'] = '1';
                break;
            case 'enable':
                $data['title'] = '启用';
                $data['class'] = ' btn-info ajax-post ';
                $data['display_position'] = '1';
                break;
            case 'disable':
                $data['title'] = '禁用';
                $data['class'] = ' btn-warning ajax-post ';
                $data['display_position'] = '1';
                break;
            case 'edit':
                $data['title'] = '修改';
                $data['class'] = ' btn-success ';
                $data['display_position'] = '2';
                break;
            case 'del':
                $data['title'] = '删除';
                $data['class'] = ' btn-danger ajax-post confirm ';
                $data['display_position'] = '1';
                break;
            case 'count':
                $data['title'] = '统计';
                $data['class'] = ' btn-primary ajax-post ';
                $data['display_position'] = '1';
                break;
            case 'recommend':
                $data['title'] = '推荐';
                $data['class'] = ' bg-purple ajax-get ';
                $data['display_position'] = '1';
                break;
            case 'no_recommend':
                $data['title'] = '取消推荐';
                $data['class'] = ' bg-navy ajax-get ';
                $data['display_position'] = '1';
                break;
            default:
                return array();
        }
        $data['url'] = $data['url'] . '/' . $action;
        return $data;
    }

    /**
     * 清除缓存
     * 
     * @param string $id
     *            当前ID
     * @author 秦晓武
     *         @time 2016-08-02
     */
    public function clean_cache($id = '')
    {
        session("menu_result", null);
        parent::clean_cache();
    }
}
