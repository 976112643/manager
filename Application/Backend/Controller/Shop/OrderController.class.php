<?php
/**
 * 商城管理-订单管理类
 */
namespace Backend\Controller\Shop;

use Backend\Controller\Base\AdminController;
use Common\Help\PushHelp;

class OrderController extends AdminController
{
    /**
     * //订单状态:  
     * 10:未付款, 
     * 20:未入座(已付款), 
     * 21:申请退款,
     * 30:待上菜(已发货),
     * 40:菜已上齐(已确认收货),
     * 41:投诉，
     * 42:开始处理投诉 
     * 43：驳回
     * 45 ： 投诉成立
     * 49:评价成功
     * 50,交易成功，
     * 90:已退单,
     * 99，已取消
     */
     
    public $status_code = array(
        '10'=>'待支付',
        '20'=>'待接单',
        '22'=>'排队中',
        '30'=>'任务进行中',
        //'40'=>'待评价',
        '45'=>'接单人已完成',
        '50'=>'任务完成',
        '90'=>'任务关闭',
        '99'=>'已取消',
        //'100'=>'订单删除',
    );
    
    public $export_name = '订单列表';
    
    /**
     * 平台商户查询条件
     */
    protected function shop_search_map()
    {
        $member_info = session('member_info');
        /** 店长 */
        if($member_info['role_id'] =='26'){
            $map['shop_id'] = $member_info['shop_id'];
        }
        /** 店长以上权限 */
        $shop_id = I('shop_id','0','int');
        if($shop_id){
            $map['shop_id'] = $shop_id;
        }else{
            //$map['shop_id'] = reset($this->all_shop())['id'];
        }
        return $map;
    }
    
    
    /**
     * 列表
     */
    public function index()
    {
        $this->get_list();
        $this->display();
    }
    
    /**
     * 搜索列表
     */
    protected function get_list($method = 'page')
    {
        //$map = $this->shop_search_map();
        $get = I('get.');
        /**禁用*/
        if(strlen(I('is_hid'))){
            $map['is_hid'] = I('is_hid');
        }
        
        /**禁用*/
        if(strlen(I('nickname'))){
            $map['nickname'] = array('like','%' . trim(I('nickname')) . '%');
        }
        
        /**用户手机号*/
        if(strlen(trim(I('mobile')))) {
            $map['mobile'] = array('like','%' . trim(I('mobile')) . '%');
        }
        
        /**订单编号*/
        if(strlen(trim(I('order_no')))) {
            $map['order_no'] = array('like','%' . trim(I('order_no')) . '%');
        }
        
        /**订单状态*/
        if(strlen(trim(I('status')))) {
            $map['status'] = array('like','%' . I('status') . '%');
        }
        /**
         * 下单时间
         */
        $start=!empty($get['start'])?$get['start']:date('Y-m-d H:i:s',0);
        $end=!empty($get['end'])?$get['end'].' 23:59:59':date('Y-m-d H:i:s',time());
        $map['add_time']  = array('between',$start.','.$end);

        $map['is_hid'] = array('IN',array(0,1));
        $map['is_del'] = array('IN',array(0,1));
        
        switch ($method)
        {
            case 'page':
            $result = $this->page(D('OrderShopMemberView'),$map,'id desc');
            foreach($result['list'] as $key=>$row){
                $row['status_text'] = $this->status_code[$row['status']];
                $result['list'][$key] = $row;
            }
            //dump($result);die;
            $result['status'] = $this->status_code;
            //$result['shop'] = array_to_select($this->all_shop(), I('shop_id'));
                
            $this->assign($result);
                break;
            case 'all':
                /** 导出*/
                $res = get_result(D('OrderShopMemberView'),$map,'id desc');
                foreach($res as $key=>$row){
                    $row['status_text'] = $this->status_code[$row['status']];
                    $res[$key] = $row;
                }
                if($res){
                    array_walk($res, function(&$a){
                        $a = $this->get_area_str($a);
                    });
                }
                break;
            default:
                $res = array();
                break;
        }
        return $res;
    }

    /**
     * 导出配置
     */
    protected function get_export_config()
    {
        $config = array(
            array('title'    =>'订单号','name'     =>'order_no','size'     =>20,'callback' =>''),
            array('title'    =>'用户昵称','name'     =>'nickname','size'     =>20,'callback' =>''),
            array('title'    =>'用户手机','name'     =>'mobile','size'     =>20,'callback' =>''),
            array('title'    =>'店铺名称','name'     =>'shop_title','size'     =>20,'callback' =>''),
            array('title'    =>'联系人','name'     =>'contact_people','size'     =>20,'callback' =>''),
            array('title'    =>'联系方式','name'     =>'contact_tel','size'     =>20,'callback' =>''),
            array('title'    =>'价格','name'     =>'money_real','size'     =>20,'callback' =>''),
            array('title'    =>'生成时间','name'     =>'add_time','size'     =>20,'callback' =>''),
            array('title'    =>'订单状态','name'     =>'status_text','size'     =>20,'callback' =>''),
        );
        return $config;
    }
    /**
     * 删除图片
     * @return [type] [description]
     */
    public function del_img()
    {
        $ids = I('id');
        if( $ids < 1) $this->error('删除失败');
        $info = get_info('order_image',array('id'=>$ids));
        //@unlink($info['image']);
        delete_data('order_image',array('id'=>$ids));
        $this->success('删除成功');
    }
    /**
     * 详情
     */
    public function details()
    {
        $order_id = I('get.ids');
        $order_info = get_info(D('OrderShopMemberView'),array('a.id'=>$order_id));

        if( $order_info['is_img'] ){
            $img = get_result('order_image',array('order_id'=>$order_id),'id desc','id,image');
            /** 图片转成全路径 */
            array_walk($img, function(&$a){ $a['image'] = file_url($a['image']);});
        }else{
            $img = array();
        }
        $order_info['order_image'] = $img;
        

        $order_detail = get_result('order_detail',array('order_id'=>$order_id));
        
        foreach($order_detail as $key=>$row){
            $row['status_text'] = $this->product_status_code[$row['status']];
            $order_detail[$key]=$row;
        }
        /*获取订单的日志*/
        $l_map['order_id'] = $order_id;
        $log = get_result(D('OrderlogView'),$l_map,'a.id desc');
        $log = int_to_string($log, $map = array('status'=>$this->status_code));
        
        /*订单状态*/
        $order_info['status_text'] = $this->status_code[$order_info['status']];
        
        
        $this->assign('log',$log);
        $this->assign('info',$order_info);
        $this->assign('order_detail',$order_detail);
        
        $this->display();
    }
    /**
     * 订单关闭
     */
    public function over()
    {
        $ids = I('ids');
        if(!is_numeric($ids))  $this->error("非法操作");
        $map = array(
            'id' =>$ids
        );
        $demand_result = get_info(D('DemandOrderView'),$map);
        if(!$demand_result) $this->error('不存在的订单');
        $status = array(
            '10','45','50','90','99'
        );
        if( in_array($demand_result['status'],$status)){
            $this->error('该订单不允许被关闭');
        }
        /** 关闭订单状态，判断是否支付，然后去退款给用户 */
        $model = M();
        $model->startTrans();
        try{
            $map = array(
                'id' =>$ids
            );
            $res = update_data('order',[],$map,array('status'=>'90'));
            if(!is_numeric($res)){
                throw new \Exception("关闭订单异常", 1);
            }
            /** 判断该订单是否需要退款 */
            if($demand_result['status'] >= 20 && $demand_result['status'] <=30){
                M('member')->where(array('id'=>$demand_result['member_id']))->setInc('balance',$demand_result['money_total']);
            }
            /** 订单记录 */
            $order_data = array(
                'order_no' =>$demand_result['order_no'],
                'type' => 24,
                'from_member_id'=> 0,
                'to_member_id' =>$demand_result['member_id'],
                'money' => $demand_result['money_total'],
                'remark'=> '订单关闭',
                'status' => 90,
                //'add_time'=>date('Y-m-d H:i:s',time())
            );
            $res_1 = update_data('capital_record',[],[],$order_data);
            if(!is_numeric($res_1)){
                throw new \Exception("添加系统日志异常", 1);
            }
            /*销售记录*/
            $sale_data = array(
                'type'=>1,
                'order_id'=>$demand_result['id'],
                'order_money'=>$demand_result['money_total'],
                'order_no'=>$demand_result['order_no'],
            );
            $res_2 = update_data('sales_log',[],[],$sale_data);
            if(!is_numeric($res_2)){
                throw new \Exception("添加销售记录异常", 1);
            }
        }catch(\Exception $e){
            $model->rollback();
            $this->error($e->getMessage());
        }
        $model->commit();
        $info = $demand_result;
        $demand_result = null;
        $info['type'] = 'send';
        $info['status'] = 90;
        $send_info = array(
           'id'=>$info['member_id'],    //[人员ID 或者'all']
           'title'=>'您发布的任务"'.$info['detail_title'].'"已被关闭,点击查看！',
           'text' =>$info['description'],
           'content' =>$info,  //[推送通知内容]
           'type'=>'send'     //和客户端协议的类型
        );
        $PushHelp = new PushHelp();
        $result = $PushHelp->Jg_push($send_info);
        $this->success('关闭成功',U('index'));
    }
}
?>