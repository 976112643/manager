<?php
namespace Backend\Controller\Message;

use Common\Help\PushHelp;
/**
 * 系统消息
 * 
 * @author 秦晓武
 *         @time 2016-08-25
 */
class SystemController extends IndexController
{

    /**
     * 表名
     * 
     * @var string
     */
    protected $table = 'message_send';

    /**
     * 列表函数
     */
    public function index()
    {
        $map = array();
        $map['type'] = array(
            'between',
            '10,19'
        );
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
        $result = $this->page($this->table, $map, 'addtime desc');
        $all_admin = get_no_del('admin');
        $all_type = $this->get_type();
        array_walk($result['list'], function (&$item) use ($all_admin, $all_type) {
            $item['member_text'] = $all_admin[$item['member_id']]['username'];
            $item['type_text'] = $all_type[$item['type']]['value'];
        });
        $this->assign($result);
        $this->display();
    }

    /**
     * 类型映射函数
     * @param int $type 类型ID，10-19为系统消息，其它类型预留
     * 
     * @return string 对应值
     */
    private function get_type($type = '')
    {
        $data = array(
            '10' => array(
                'value' => '全局广播',
                'remark' => '添加时发送给所有前台用户，无法修改'
            ),
            // '11' => array(
            //     'value' => '注册时触发',
            //     'remark' => '用户注册后自动接收'
            // )
        );
        
        return $type ? $data[$type] : $data;
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
        $data['all_type'] = $this->get_type();
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
        $rules[] = array('title','1,40','标题不得少于1个且不得多于40个','0','length');
        $rules[] = array(
            'content',
            'require',
            '内容必须填'
        );
        $rules[] = array(
            'type',
            array(
                10,
                19
            ),
            '分类不合法',
            1,
            'between'
        );
        $result = update_data($this->table, $rules, array(), $data);
        if (is_numeric($result)) {
            if (I('type') == 10 && (ACTION_NAME == 'add')) {
                $data['id'] = $result;
                $this->send_10($data);
            }
            $this->success('操作成功', U('index'));
        } else {
            $this->error($result);
        }
    }

    /**
     * 发送全局广播
     * @param array $data 内容数组
     * @author 秦晓武
     * @time 2016-08-25
     */
    private function send_10($data)
    {
        $base = array(
            'type' => 'all',
            'id' => 'all',
            'title' => $data['title'],
            'text' =>$data['title'],
            'content' => $data['content'],
        );
        $push = new PushHelp();
        $push->Jg_push($base);
    }
    
    
    /**
     * 消息列表详情
     * @return [type] [description]
     */
    public function message_list(){
        $keywords=I('keywords');
        if($keywords) {
            $map['title'] = ['like','%' . trim($keywords) . '%'];
        }
        $is_read=I('is_read','-1','int');
        if($is_read>=0) {
            $map['is_read'] = $is_read;
        }
        $map['member_id']=0;
        $data=$this->page('message_receive',$map,'is_read asc,addtime desc,id asc');
        $this->assign($data);
        $this->display('list');
    }
    
    
    /**
     * 系统消息删除
     * @return [type] [description]
     */
    public function del(){
        $id=I('ids');
        if(is_array($id)){
            $map['id']=array('in',$id);
        }else{
            $map['id']=$id;
        }
        update_data('message_receive','',$map,['is_del'=>1]);
        //parent::del();
        $this->success('操作成功', U('message_list'));
    }
    
    /**
     * 消息详情
     * @return [type] [description]
     */
    public function detail(){
        $id=I('ids','','int');
        $info = get_info('message_receive',['id'=>$id]);
        if($info){
            update_data('message_receive','','',['id'=>$id,'is_read'=>'1']);
        }
        $data['info'] = $info;
        $this->assign($data);
        $this->display();
    }
    /**
     * 标记已读
     */
    public function read()
    {
        $id=I('ids');
        if(is_array($id)){
            $map['id']=array('in',$id);
        }else{
            $map['id']=$id;
        }
        update_data('message_receive','',$map,['is_read'=>1]);
        //parent::del();
        $this->success('操作成功', U('message_list'));
    }
}
