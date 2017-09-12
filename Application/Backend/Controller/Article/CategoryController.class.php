<?php
namespace Backend\Controller\Article;

/**
 * 分类主类
 * 
 * @author 秦晓武
 *         @time 2016-06-06
 */
class CategoryController extends IndexController
{

    /**
     * 表名
     * 
     * @var string
     */
    protected $table = 'category';

    /**
     * 列表示例，子类自行复写
     * 
     * @author 秦晓武
     *         @time 2016-05-31
     */
    public function index()
    {
        $map = array();
        /* 分类 */
        if (strlen(I('category_id'))) {
            $map['id'] = array(
                'in',
                get_all_child_ids(get_no_del($this->table), I('category_id'))
            );
        }
        /* 数据少无需分页(和筛选不兼容) */
        $no_page = true;
        if ($no_page) {
            $result['list'] = get_result($this->table, $map, 'sort desc');
            $tree = array_to_tree($result['list'], array(
                'root' => I('category_id') ? I('category_id') : 0,
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
        } else {
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
            $result['list'] = $this->page($this->table, $map, 'sort desc');
        }
        $result['category'] = array_to_select(get_no_del($this->table), I('category_id'));
        $this->assign($result);
        $this->display();
    }
}
