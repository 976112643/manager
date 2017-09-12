<?php
/**
 * 菜单列表页功能
 * @package 
 * @time 2014-12-26
 * @author 郭文龙 <2824682114@qq.com>
 */
namespace Backend\Controller\System;

/**
 * 菜单列表页类
 * 
 * @package @time 2014-12-26
 * @author 郭文龙 <2824682114@qq.com>
 */
class SeoPageController extends IndexController
{

    /**
     * 表名
     */
    protected $table = 'Seo_page';

    /**
     * 功能初始化
     * @time 2014-12-26
     * 
     * @author 郭文龙 <2824682114@qq.com>
     */
    protected function _init()
    {
        session('table', $this->table);
    }

    /**
     * 菜单列表页
     * @time 2014-12-26
     * 
     * @author 郭文龙 <2824682114@qq.com>
     */
    public function index()
    {
        $map['is_del'] = array(
            'eq',
            0
        );
        $keywords = I('keywords');
        if ($keywords) {
            $map['title'] = array(
                'like',
                "%$keywords%"
            );
        }
        
        $this->page($this->table, $map, 'sort asc', true, 20);
        $this->display();
    }

    /**
     * 添加操作
     * @time 2014-12-26
     * 
     * @author 郭文龙 <2824682114@qq.com>
     */
    public function add()
    {
        if (IS_POST) {
            $this->update();
            F('seo_page', NULL);
        } else {
            $this->display('operate');
        }
    }

    /**
     * 修改操作
     * @time 2014-12-26
     * 
     * @author 郭文龙 <2824682114@qq.com>
     */
    public function edit()
    {
        if (IS_POST) {
            $this->update();
            F('seo_page', NULL);
        } else {
            $id = intval(I('ids'));
            $map['id'] = $id;
            $data['info'] = M($this->table)->where($map)->find();
            $this->assign($data);
            $this->display('operate');
        }
    }

    /**
     * 添加/修改操作
     * @time 2014-12-26
     * 
     * @author 康利民 <3027788306@qq.com>
     */
    public function update()
    {
        if (IS_POST) {
            $id = intval(I('post.id'));
            $rules = array(
                array(
                    'title',
                    'require',
                    '请填写页面名称'
                ),
                array(
                    'url',
                    'require',
                    '地址信息必须填写'
                ),
                array(
                    'page_title',
                    'require',
                    '页面标题必须填写'
                )
            );
            $result = update_data($this->table, $rules);
            if (is_numeric($result)) {
                F('seo_page', NULL);
                $this->success('操作成功', U('index'));
            } else {
                $this->error($result);
            }
        } else {
            $this->success('违法操作', U('index'));
        }
    }
}