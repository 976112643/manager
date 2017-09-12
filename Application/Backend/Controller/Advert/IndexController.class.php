<?php
/**广告管理*/
namespace Backend\Controller\Advert;

use Backend\Controller\Base\AdminController;

/**
 * 广告管理
 */
class IndexController extends AdminController
{

    /**
     * 表名
     * 
     * @var string
     */
    protected $table = 'banner';
    /**
     * 设置类型
     */
    protected $type = array(
        '10' =>'图片',
        // '20' =>'HTML代码'
    );
    /**
     * APP类型
     * 10 用户端
     * 20 工人端
     */
    protected $app_type = '10';
    /**
     * 广告位列表
     * 
     * @param string $type
     *            类型
     *            @time 2015-03-12
     * @author 康利民 <2824682114@qq.com>
     *        
     */
    public function index()
    {
        $this->get_list();
        $this->display('Advert/Index/lists');
    }
    /**
     * 获取首页数据
     * @param   $[method] [获取数据方法]
     */
    protected function get_list($method = 'page')
    {
        $map = array(
            'app_type' =>$this->app_type
        );
        $type = $this->type;
        switch ($method){
            case 'page':
                $res = $this->page($this->table,$map,'sort desc,id asc');
                array_walk($res['list'], function(&$a) use($type){
                    $a['type_text'] = $type[$a['type']];
                });
                break;
            case 'all':
                
                break;
            default :
                break;
        }
        return $res;
    }
    /**
     * 添加菜单
     */
    public function add()
    {
        if (IS_POST) {
            unset($_POST['id']);
            $this->update();
        } else {
            $this->operate();
        }
    }

    /**
     * 修改菜单
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
     * 添加/修改操作
     */
    public function update()
    {
        if (IS_POST) {
            $posts = I('post.');
            dump($posts);exit;
            if ($posts['type'] == '10' && $posts['old_image'] == '' && $posts['image'] == "") {
                $this->error("图片不能为空！");
            }
            if ($posts['type'] == '20' && $posts['content'] == "") {
                $this->error("HTML代码不能为空！");
            }
            if ($posts['start_time'] == '') {
                $_POST['start_time'] = date("Y-m-d 00:00:00");
            }
            if ($posts['end_time'] == '') {
                $_POST['end_time'] = date("Y-m-d 00:00:00", strtotime('+1 day'));
            }
            
            $rules = array()
            // array('title','require','请填写广告标题！'), //默认情况下用正则进行验证
            ;
            $result = update_data($this->table, $rules);
            if (is_numeric($result)) {
                if ($result > 0) {
                    $id = $result;
                } else {
                    $id = intval(I('post.id'));
                }
                if ($id > 0) {
                    if (I('post.image') != '') {
                        del_thumb($_POST['old_image']);
                    }
                    multi_file_upload(I('post.image'), 'Uploads/Banner', $this->table, 'id', $id, 'save_path');
                }
                $this->clean_cache();
                $this->success('操作成功！', U('index'));
            } else {
                $this->error($result);
            }
        } else {
            $this->success('违法操作', U('index'));
        }
    }

    /**
     * 删除广告数据，同时删除广告图片
     * 
     * @param int $ids
     *            数据ID
     *            @time 2015-01-13
     * @author 康利民 <3027788306@qq.com>
     */
    function del($ids = 0)
    {
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $this->doDel($id);
            }
        } else {
            $id = $ids;
            $this->doDel($id);
        }
        $this->clean_cache();
        $this->success('删除成功');
    }

    /**
     * 删除处理
     * 
     * @param int $ids
     *            数据ID
     */
    function doDel($id = 0)
    {
        $map['id'] = $id;
        $info = get_info($this->table, $map);
        if ($info && $info['type'] == "save_path") {
            del_thumb($info['save_path']);
        }
        $de = delete_data($this->table, $map);
    }

    /**
     * 删除处理
     */
    public function ajaxDelete_banner()
    {
        $posts = I("post.");
        $info = get_info($this->table, array(
            "id" => $posts['id']
        ));
        $path = $info['save_path'];
        $_POST = null;
        $this->clean_cache();
        if (file_exists($path)) {
            $result = del_thumb($path);
            if ($result) {
                $this->success("删除成功");
            } else {
                $this->error("删除失败");
            }
        } else {
            $_POST['id'] = $posts['id'];
            $_POST['save_path'] = '';
            update_data($this->table, array(
                "id" => $posts['id']
            ));
            $this->success("文件不存在，删除失败，数据被清空");
        }
    }

    /**
     * 改变状态
     * 
     * @param string $field
     *            更新字段
     */
    function setStatus($field = "status")
    {
        $adspace_id = I("adspace_id");
        $ids = I('ids');
        $field_val = intval(I('get.' . $field));
        if (is_array($ids)) {
            $_POST = array(
                $field => $field_val
            );
            $map['id'] = array(
                'in',
                $ids
            );
            M($this->table)->where($map)->save($_POST); // 根据条件更新记录
        } else {
            $ids = intval($ids);
            $_POST = array(
                'id' => $ids,
                $field => $field_val
            );
            $result = update_data($this->table);
            if (! is_numeric($result)) {
                $this->error($result);
            }
        }
        $this->success('操作成功', U('index'));
    }
    /**
     * 清理F缓存
     * @param   $[id] [主键]
     */
    public function clean_cache($id = '')
    {
        F('banner_'.$this->app_type,null);
        parent::clean_cache();
    }
    /**
     * 详情页
     */
    private function operate()
    {
        $ids = I('ids');
        if($ids){
            $info = get_info($this->table,array('id'=>$ids));
            $this->assign('info',$info);
        }
        $this->assign('type',$this->type);
        $this->display('Advert/Index/operate');
    }
}
