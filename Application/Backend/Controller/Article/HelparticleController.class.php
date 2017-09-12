<?php
namespace Backend\Controller\Article;

/**
 * 帮助中心
 * 
 * @author 秦晓武
 *         @time 2016-06-01
 */
class HelparticleController extends ArticleController
{

    /**
     * 表名
     * 
     * @var string
     */
    protected $table = 'help';

    /**
     *
     * @var string 分类表名
     */
    protected $category = 'help_category';

    /**
     * 列表函数
     */
    public function index()
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
            $map['category_id'] = I('category_id');
        }
        $result = $this->page($this->table, $map, 'sort desc');
        $all_category = get_no_del($this->category);
        array_walk($result['list'], function (&$item) use ($all_category) {
            $item['category_text'] = array_to_crumbs($all_category, $item['category_id']);
        });
        $result['category'] = array_to_select($all_category, I('category_id'));
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
            '标题必须填'
        );
        $rules[] = array(
            'content',
            'require',
            '内容必须填'
        );
        $rules[] = array(
            'category_id',
            'require',
            '分类必须填'
        );
        $rules[] = array('title','1,40','标题不得少于1个且不得多于40个','0','length');
        $rules[] = array('sort','0,9999','排序范围必须在0~9999之间','0','between');
        $result = update_data($this->table, $rules, array(), $data);
        if (is_numeric($result)) {
            $this->success('操作成功', U('index'));
        } else {
            $this->error($result);
        }
    }
}
