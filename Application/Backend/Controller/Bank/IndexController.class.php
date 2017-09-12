<?php
namespace Backend\Controller\Bank;

use Backend\Controller\Base\AdminController;
/**
 * 平台支持的银行记录
 */
class IndexController extends AdminController
{
    /**
    * 表名 银行
    * @var string
    */
    protected $table='bank';
    /**
     * 列表
     */
    public function index()
    {
        $map = $this->map();
        $this->page($this->table,$map,'id asc',true,30);
        $this->display();
    }
    /**
     * 查询条件
     */
    public function map()
    {
        
    }
    /**
     * 添加
     */
    public function add()
    {
        if(IS_POST){
            unset($_POST['id']);
            $this->update();
        }else{
            $this->operate();
        }
    }
    /**
     * 修改
     */
    public function edit()
    {
        if(IS_POST){
            $this->update();
        }else{
            $this->operate();
        }
    }
    /**
     * ajax删除
     */
    public function ajaxDelete_bank()
    {
        $posts = I ( "post." );
        $info = get_info ( $this->table, array ("id" => $posts ['id']));
        $path = array('img');
        if(in_array($posts['name'],$path)){
            $name = $info[$posts['name']];
            if(file_exists($name)){
                delImgAll($name);
                $info[$posts['name']] = '';
                update_data($this->table,'','',$info);
                $this->success('删除成功');
            }else{
                $info[$posts['name']] = '';
                update_data($this->table,'','',$info);
                $this->success ( "文件不存在，删除失败，数据被清空" );
            }
        }
    }
    /**
     * 更新
     */
    private function update()
    {
        $data = I('post.');
        $res = update_data($this->table);
        if(is_numeric($res)){
            multi_file_upload($data['img'], 'Uploads/Bank', 'bank', 'id', $res,'img');
            $this->success('操作成功',U('index'));
        }else{
            $this->error($res);
        }
    }
    /**
     * 详情
     */
    private function operate()
    {
        $ids = I('ids');
        if($ids){
            $info = get_info($this->table,array('id'=>$ids));
            $this->assign('info',$info);
        }
        $this->display('operate');
    }
}
?>