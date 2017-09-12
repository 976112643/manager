<?php
namespace Api\Controller\Push;
use Api\Controller\Base\BaseController;
use Common\Help\PushHelp;
use Common\Help\Geohash;
use Common\Plugin\DemandRedis;
use Common\Help\WorkerPushHelp;
/**
 * 消息推送类
 */
class MsgpushController extends BaseController
{
    protected $demand_info; /*需求内容*/
    protected $demand_address; /*需求地址*/
    protected $demand_address_lanlat; /*需求经纬度*/
    /**
     * 子类集成
     * 
     * @return [type] [description]
     */
    protected function __init()
    {
        parent::__init();
        $this->demand_info = $this->__init_demand(); //载入需求
        $this->demand_address = $this->get_demand_address(); //载入地址
        $this->demand_address_lanlat = $this->get_demand_address_lanlat(); //载入经纬度
    }
    
    private function __init_demand(){
        /*获取最新一条需求*/
        $info = get_info('demand',array('is_push'=>0,'is_hid'=>0,'is_del'=>0,'status'=>10),true,'id asc');
        return $info;
    }
    
    public function index(){
        //dump($this->get_demand_address_lanlat());
    }
    
    /**
     * 获取需求
     * @author 鲍海
     * @time 2017-03-27
     */
    private function get_demand_address(){
        return $this->demand_info['address'];
    }
    
    /**
     * 根据需求地址获取需求经纬度
     * @time 2017-03-27
     */
    public function get_demand_address_lanlat(){
        return get_lat_log($this->demand_address);
    }
    
    /**
     * 获取工人经纬度列表
     * @author 鲍海
     * @time 2017-03-27
     */
    public function get_gmember_lanlat(){
        $arr = [];
        $redis = new DemandRedis();
        $arr = $redis->get_all_member_local();
        /*获取需求地址经纬度*/
        $demand_info = $this->demand_info;
        //$get_demand_address_lanlat = $this->get_demand_address_lanlat();
        $lan = $demand_info['longitude'];
        $lat = $demand_info['latitude'];
        
        /*计算经纬度与用户之间的距离*/
        $distance_arr = [];
        foreach($arr as $key=>$row){
           $distance =  getDistance($lat, $lan, $row['latitude'], $row['longitude']);
           $distance_arr[$distance][] = array('id'=>$key,'info'=>$row);
        }
        /*对距离进行升序排列*/
        ksort($distance_arr);
        return $distance_arr;
    }
    
    /**
     * 发送需求推送信息
     * @author 鲍海
     * @time 2017-04-06
     */
    public function send($data=array(),$type='JG'){
        $PushHelp = new WorkerPushHelp();
        //$PushHelp = new PushHelp();
        $type = strtoupper($type);
        $gmeber_ids = $this->get_gmember_lanlat();
        $demand_info = $this->demand_info;
        //dump($gmeber_ids);die;
        if($demand_info){
            /*已支付，待接单的订单*/
            $order_info = get_info(D('OrderShopMemberView'),array('id'=>$demand_info['order_id'],'status'=>20));
            if($order_info['id']){
                $category_remark = json_decode($order_info['category_remark'],true);
                $category_remark_text = '';
                foreach($category_remark as $row){
                    $category_remark_text.=$row['title'].',';
                }
                /** 发送订单推送消息*/
                $order_remark = [];
                $order_remark['order_id'] = $order_info['id'];
                $order_remark['order_date'] = $order_info['door_time'];
                $order_remark['head_img'] = $this->_host.$order_info['m_head_img'];
                $order_remark['nickname'] = $order_info['nickname'];
                $order_remark['order_price'] = $order_info['money_total'];
                $order_remark['order_no'] = $order_info['order_no'];
                $order_remark['category_remark'] = $category_remark_text;
                $order_remark['type'] = 'order';
                switch($type){
                    case 'JG': //极光推送
                        if(count($gmeber_ids)){
                            foreach($gmeber_ids as $key=>$row){
                                foreach($row as $v){
                                    $order_remark['distance'] = $key;
                                    $info = array(
                                       'id'=>$v['id'],    //[人员ID 或者'all']
                                       'title'=>'您有新的订单来了',
                                       'text' =>'您有新的订单来了,订单号为 '.$order_info['order_no'].' 订单金额:￥'.$order_info['money_total'].' 实际支付:￥'.$order_info['money_total'],
                                       'content' =>$order_remark,  //[推送通知内容]
                                       'type'=>'sina'     //和客户端协议的类型
                                    );
                                    //dump($info);die;
                                    $result = $PushHelp->Jg_push($info);
                                    usleep(1000);
                                }
                            }
                        }
                        break;
                    default :
                        //数据库message
                        break;
                }
            }
            $demand_data = array();
            $demand_data['is_push'] = 1;
            $demand_data['id'] = $demand_info['id'];
            $res = update_data('demand',[],[],$demand_data);

        }
    }
}


?>