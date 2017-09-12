<?php
/**
 * 导航
 * @package
 * @author 秦晓武
 */
namespace Backend\Controller\Navigation;

use Backend\Controller\Base\AdminController;

/**
 * 导航
 * 
 * @package
 *
 * @author 秦晓武
 */
class IndexController extends AdminController
{

    /**
     * 表名
     * 
     * @var string
     */
    protected $table = 'navigation';

    /**
     * 类型
     * 
     * @var string
     */
    protected $type = '';

    /**
     * 头部导航列表页
     * 1、显示所有头部导航（未删除）；
     * 2、支持状态筛选，和关键字搜索
     * 
     * @author 秦晓武
     */
    public function index()
    {
        $map['type'] = $this->type;
        /* 状态 */
        if (strlen(I('is_hid'))) {
            $map['is_hid'] = I('is_hid');
        }
        /* 获取搜索关键字 */
        if (strlen(trim(I('search')))) {
            $map['title'] = array(
                'like',
                '%' . I('search') . '%'
            );
        }
        /* 获取数据集 */
        $data['list'] = get_result('navigation', $map, 'sort desc');
        $tree = array_to_tree($data['list'], array(
            // 'root' => $this->get['category_id']?$this->get['category_id']:0,
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
        $data['list'] = tree_to_array($tree);
        
        $this->assign($data);
        /* 加载页面 */
        $this->display('Navigation/index');
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
     * 详情页，默认二级，由limit控制
     * 
     * @author 秦晓武
     */
    public function operate()
    {
        $data['info'] = get_info($this->table, array(
            'id' => I('ids')
        ));
        $all = get_result($this->table, array(
            'type' => $this->type
        ));
        $data['info']['pid'] = array_to_select($all, $data['info']['pid'], array(
            'limit' => 0
        ));
        $this->assign($data);
        $this->display('Navigation/operate');
    }

    /**
     * 添加、修改函数
     * 
     * @author 秦晓武
     */
    public function update()
    {
        $data = I('post.');
        $data['member_id'] = session('member_id');
        $data['type'] = $this->type;
        /* 验证参数 */
        $rules[] = array(
            'title',
            'require',
            '标题必须填'
        );
        $result = update_data($this->table, $rules, $map, $data);
        if (is_numeric($result)) {
            $this->success('操作成功', U('index'));
        } else {
            $this->error($result, U('index'));
        }
    }
}
