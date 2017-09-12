<?php
namespace Backend\Controller\Article;

/**
 * 资讯文章分类
 * 
 * @author 秦晓武
 *         @time 2016-06-01
 */
class NewscategoryController extends CategoryController
{

    /**
     * 表名
     * 
     * @var string
     */
    protected $table = 'news_category';

    /**
     * 列表
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
        $info['pid'] = array_to_select(get_no_del($this->table), $info['pid']);
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
        $data['member_id'] = session('member_id');
        /* 验证参数 */
        $rules[] = array(
            'title',
            'require',
            '标题必须填'
        );
        $result = update_data($this->table, $rules, array(), $data);
        if (is_numeric($result)) {
            multi_file_upload(I('icon'), 'Uploads/News', $this->table, 'id', $result, 'icon');
            $this->success('操作成功', U('index'));
        } else {
            $this->error($result);
        }
    }

    /**
     * 统计
     * 
     * @author 秦晓武
     *         @time 2016-05-31
     */
    function count()
    {
        $result = M()->execute('
			UPDATE sr_news_category
			LEFT JOIN (
				select category_id, count(*) as num 
				from sr_news 
				where is_del = 0 
				group by category_id
			) temp ON sr_news_category.id = temp.category_id
			SET sr_news_category.num = if(temp.num is null,0,temp.num)
		');
        if (is_numeric($result)) {
            $this->success('更新成功');
        } else {
            $this->error($result);
        }
    }
}
