<?php

namespace Backend\Controller\Version;

use Backend\Controller\Base\AdminController;
/**
 * 商户端APK管理
 */
class IndexController extends AdminController
{
    /** 操作表*/
    protected $table = 'app_version';
    /** 类型*/
    protected $type = array(
        '10' => '安卓APK',
        '20' => 'IOS'
    );

    /**
     * 列表
     */
    public function index()
    {
        $this->page($this->table,array('app_type'=>1));
        $this->display();
    }

    /**
     * 添加
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
     * 修改
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
     * 删除文件
     */
    public function ajaxDelete_app_version()
    {
        $posts = I("post.");
        $table = $table ? $table : $this->table;
        $info = get_info($table, array(
            "id" => $posts['id']
        ));
        if ($info) {
            $name = $info['path'];
            if (file_exists($name)) {
                @unlink($name);
                $info['path'] = '';
                update_data($table, '', '', $info);
                $this->success('删除成功');
            } else {
                $info['path'] = '';
                update_data($table, '', '', $info);
                $this->success("文件不存在，删除失败，数据被清空");
            }
        }
    }

    /**
     * 读取配置
     */
    private function operate()
    {
        $ids = I('ids');
        $info = get_info($this->table, array(
            'id' => $ids
        ));
        $this->assign('info', $info);
        $this->display('operate');
    }

    /**
     * 更新
     */
    private function update()
    {
        $data = I('post.');
        $rule = $this->rule();
        if($data['type']==10){
            $rule[] = array(
                'path',
                'require',
                '请上传应用文件'
            );
        }else{
            if(!preg_match(URL,$data['url']))$this->error('错误的URL格式');
            $rule[] = array(
                'url',
                'require',
                '请填写应用URL'
            );
        }
        //$data['type'] = 10;
        $res = update_data($this->table, $rule, array(), $data);
        if (is_numeric($res)) {
            if($data['type']==10){
                multi_file_upload($data['path'], 'Uploads/APP', $this->table, 'id', $res, 'path');
            }
            $this->del_qrcode();
            $this->success('操作成功', U('index'));
        } else {
            $this->error($res);
        }
    }

    /**
     * 验证规则
     */
    private function rule()
    {
        $rule = array(
            array(
                'name',
                'require',
                '请填写应用名称'
            ),
            array(
                'type',
                'require',
                '请选择应用类型'
            ),
            array(
                'version',
                'require',
                '请填写版本号'
            ),
            array(
                'name',
                '1,20',
                '请输入1-20位的应用名称',
                1,
                'length'
            ),
            array(
                'version',
                '1,20',
                '请输入1-20位的版本号',
                1,
                'length'
            )
        );
        return $rule;
    }
    /**
     * 删除缓存文件
     */
    public function del_qrcode()
    {
        $file = 'Uploads/APP/qrcode.png';
        @unlink($file);
        $file = 'Uploads/APP/down.jpg';
        @unlink($file);
    }
}

?>