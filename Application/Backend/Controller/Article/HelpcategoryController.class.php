<?php
namespace Backend\Controller\Article;

/**
 * 帮助中心分类
 * 
 * @author 秦晓武
 *         @time 2016-06-07
 */
class HelpcategoryController extends CategoryController
{

    /**
     * 表名
     * 
     * @var string
     */
    protected $table = 'help_category';

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
        
        $result['list'] = get_result($this->table, $map, 'sort desc');
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
        $data = I();
        /* 获取前台传递的添加参数 */
        $data['member_id'] = session('member_id');
        /* 验证参数 */
        $rules[] = array(
            'title',
            'require',
            '标题必须填'
        );
        $rules[] = array('title','1,4','分类名称不得少于1个且不得多于4个','0','length');
        $rules[] = array('sort','0,9999','排序范围必须在0~9999之间','0','between');
        $result = update_data($this->table, $rules, array(), $data);
        if (is_numeric($result)) {
            multi_file_upload(I('icon'), 'Uploads/Help', $this->table, 'id', $result, 'icon');
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
			UPDATE sr_help_category
			LEFT JOIN (
				select category_id, count(*) as num 
				from sr_help 
				where is_del = 0 
				group by category_id
			) temp ON sr_help_category.id = temp.category_id
			SET sr_help_category.num = if(temp.num is null,0,temp.num)
		');
        if (is_numeric($result)) {
            $this->success('更新成功');
        } else {
            $this->error($result);
        }
    }
}
