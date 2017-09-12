<?php
/**
 * 资讯文章
 * @author 秦晓武
 * @time 2016-06-01
 */
namespace Backend\Controller\Article;

/**
 * 资讯文章
 */
class NewsarticleController extends ArticleController
{

    /**
     * 表名
     * 
     * @var string
     */
    protected $table = 'news';

    /**
     * 分类表名
     * 
     * @var string
     */
    protected $category = 'news_category';

    /**
     * 列表函数
     */
    public function index()
    {
        $map = $this->get_list_map();
        $result = $this->page($this->table, $map, 'recommend desc,sort desc');
        
        array_walk($result['list'], function (&$item) {
            $item['category_text'] = array_to_crumbs(get_no_del($this->category), $item['category_id']);
        });
        $result['category'] = array_to_select(get_no_del($this->category), I('category_id'));
        $this->assign($result);
        $this->display();
    }

    /**
     * 获取列表页过滤条件，用于和导出公用逻辑
     * 
     * @return array 过滤数组
     */
    private function get_list_map()
    {
        $map = array();
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
        
        /* 推荐 */
        if (strlen(I('recommend'))) {
            $map['recommend'] = I('recommend');
        }
        /* 分类 */
        if (strlen(I('category_id'))) {
            $map['category_id'] = array(
                'in',
                get_all_child_ids(get_no_del($this->category), I('category_id'))
            );
        }
        /* 时间 */
        $start = ! empty(I('start_date')) ? I('start_date') : 0;
        $end = ! empty(I('stop_date')) ? (I('stop_date') . ' 23:59:59') : date('Y-m-d H:i:s');
        $map['add_time'] = array(
            'BETWEEN',
            array(
                $start,
                $end
            )
        );
        
        return $map;
    }

    /**
     * 导出列表数据到EXCEL
     */
    public function export_excel()
    {
        $map = $this->get_list_map();
        $result = get_result($this->table, $map, 'recommend desc,sort desc');
        
        array_walk($result, function (&$item) {
            $item['category_text'] = array_to_crumbs(get_no_del($this->category), $item['category_id']);
            
            /* 格式化数据 */
            $this->format_value($item);
        });
        /* 填充数据 */
        $data['result'] = $result;
        /* 填充表名 */
        $data['sheetName'] = '数据表';
        $menu = get_menu_list();
        foreach ($menu as $row) {
            if ($row['url'] == MODULE_NAME . '/' . CONTROLLER_NAME . '/index') {
                $data['sheetName'] = $row['title'];
            }
        }
        A('Common/Excel', '', 1)->export_data($this->excel_config(), $data);
    }

    /**
     * 配置输入表格信息
     * @time 2016-9-18
     * 
     * @author 秦晓武
     */
    public function excel_config()
    {
        $config = array(
            array(
                'title' => '分类',
                'field' => 'category_text'
            ),
            array(
                'title' => '标题',
                'field' => 'title'
            ),
            array(
                'title' => '添加日期',
                'field' => 'add_time'
            ),
            array(
                'title' => '阅读量',
                'field' => 'views'
            ),
            array(
                'title' => '排序',
                'field' => 'sort'
            ),
            array(
                'title' => '推荐',
                'field' => 'recommend'
            ),
            array(
                'title' => '状态',
                'field' => 'is_hid'
            )
        );
        return $config;
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
        $info['category_id'] = array_to_select(get_no_del($this->category), $info['category_id']);
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
        $data['content'] = $_POST['content'];
        /* 验证参数 */
        $rules[] = array(
            'title',
            'require',
            '标题必须填',
            1
        );
        $rules[] = array(
            'title',
            '50',
            '标题不能超过50字符',
            3,
            'length'
        );
        $rules[] = array(
            'content',
            'require',
            '内容必须填',
            1
        );
        $rules[] = array(
            'category_id',
            'require',
            '分类必须填',
            1
        );
        $result = update_data($this->table, $rules, array(), $data);
        if (is_numeric($result)) {
            multi_file_upload(I('cover'), 'Uploads/News', $this->table, 'id', $result, 'cover');
            $this->success('操作成功', U('index'));
        } else {
            $this->error($result);
        }
    }
}
