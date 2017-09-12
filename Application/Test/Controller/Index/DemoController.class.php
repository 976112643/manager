<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/28 0028
 * Time: 11:36
 */
namespace Test\Controller\Index;

use Common\Controller\CommonController;
use Common\Plugin\DemandRedis;
use Common\Help\PushHelp;
use Common\Help\RedisHelp;
use Common\Plugin\Person;
use Common\Help\StrHelp;
use Common\Plugin\Pay\Pay;
use Common\Plugin\Title;
use Common\Help\ImgHelp;
use Common\Plugin\Crontab\CrontabManager;
use Common\Plugin\Comment;

class DemoController extends CommonController
{
    /**
     * 测试用例
     * 测试使用上传地理位置的接口
     */
    public function test_demand()
    {
        $redis = new DemandRedis();
        $res = $redis->join_local(63, array('lat' => '113.24', 'lng' => '34.16'));
        dump($res);
    }
    /**
     * 测试极光推送
     * @author 鲍海
     * @time 2017-04-05
     */
    public function test_jgpush(){
        if( !I('id') )  exit('请输入ID');
        $PushHelp = new PushHelp();
        //$data['id'] = 187;
        //$data['title'] = '标题';
        //$data['text'] = '描述';
        //$data['content'] = $row['内容'];
        //$data['type'] = 'sina';
        $order_info = get_info(D('OrderShopMemberView'),array('id'=>1916));
        /** 发送订单推送消息*/
        $order_remark = [];
        $order_remark['order_id'] = $order_info['id'];
        $order_remark['order_date'] = $order_info['door_time'];
        $order_remark['head_img'] = $order_info['m_head_img'];
        $order_remark['nickname'] = $order_info['nickname'];
        $order_remark['distance'] = '1km';
        $order_remark['type'] = 'receive';
        
        // $info = array(
        //    'id'=>3,    //[人员ID 或者'all']
        //    'title'=>'您有新的订单来了',
        //    'text' =>'您有新的订单来了,订单号为 '.$order_info['order_no'].' 订单金额:￥'.$order_info['money_total'].' 实际支付:￥'.$order_info['money_total'],
        //    'content' =>$order_remark,  //[推送通知内容]
        //    'type'=>'sina'     //和客户端协议的类型
        // );
        
        $order_info['type'] = 'queue';
        $order_info['title'] = '您申请接受的任务“xxxxxxx…”已经选择其他人完成了，去看看其他的吧！';
        $send_info = array(
           'id'=>I('id'),    //[人员ID 或者'all']
           'title'=>$order_info['title'],
           'text' =>$order_info['description'],
           'content' =>$order_info,  //[推送通知内容]
           'type'=>'queue'     //和客户端协议的类型
        );
        //dump($info);die;
        
        $result = $PushHelp->Jg_push($send_info);
        dump($result);
    }
    
    /**
     * 获取在需求池中的用户
     * @author 鲍海
     * @time 20170-04-05
     */
    public function get_gmember(){
        $redis = new DemandRedis();
        dump($redis->get_all_member_local());
        
        
    }
    /**
     * 测试意见反馈
     */
    public function test_back()
    {
        $id = 96;
        $cache = 'feekback:'.$id;
        $flag = $this->check_add($id,$cache);
        if($flag){
            $this->error('请不要重复提交,5秒后再尝试');
        }
        dump($flag);
    }
    /**
     * 防止恶意提交数据
     * 使用Redis阻止
     */
    protected function check_add($member_id,$cache)
    {
        $redis = RedisHelp::getInstance();
        
        $value = $redis->get($cache);
        /** 验证是否已经提交过了 */
        if($value)      return true;
        /** 如果没有提交，那么插入Redis里面，设置过期时间为5秒 */
        $value = $redis->set($cache,$member_id,5);
        return $value ? false : true;
    }
    public function test_sum()
    {
        $s = microtime(true);
        echo 'start:'.$s;
        echo '<br/>';
        $now_day_time = date('Y-m-d',time());
        $start = $now_day_time.' 00:00:00';
        $end = $now_day_time.' 23:59:59';
        $map['add_time'] = array('BETWEEN',array($start,$end));
        $map['status'] = 1;
        $balance_sum_money = get_result('member_certification_copy',$map,'','SUM(`balance_money`) as balance_sum_money');
        $e = microtime(true);
        echo 'end:'.$e;
        echo '<br/>';
        echo $e - $s;
    }
    public function test_sum_two()
    {
        $s = microtime(true);
        echo 'start:'.$s;
        echo '<br/>';
        $now_day_time = date('Y-m-d',time());
        $start = $now_day_time.' 00:00:00';
        $end = $now_day_time.' 23:59:59';
        $map['add_time'] = array('BETWEEN',array($start,$end));
        $map['status'] = 1;
        $balance_sum_money = get_result('member_certification_copy',$map,'','balance_money');
        $money = array_sum(array_column($balance_sum_money,'balance_money'));
        $e = microtime(true);
        echo 'end:'.$e;
        echo '<br/>';
        echo $e - $s;
    }
    public function test_send_code()
    {
        $res = send_sms('18062878878','999999','SMS_71305002',1);
        dump($res);
    }
    public function test_array()
    {
        $to_member_id = '1';
        $field = 'SUM(`star_rating`) as sum_star_rating,COUNT(`id`) as count_star_rating';
        $sum_comment = get_result('order_comment',array('member_id'=>$to_member_id),'',$field);
        $sum_comment = reset($sum_comment);
        $star_rating = round( $sum_comment['sum_star_rating'] / $sum_comment['count_star_rating'] , 1);
    }
    public function test_recommend()
    {
        /** 生成推广码 */
        $person = new Person();
        $person->create_recommend_code('33');
        dump($res);
    }
    public function test_str()
    {
        $str = I('str');
        dump($str);
        $first = msubstr( $str, 0, 1 ,'utf-8','');
        dump($first);
        if( !preg_match("/[a-zA-Z]/",$first) && !preg_match('/['.chr(0xa1).'-'.chr(0xff).']/',$first) ){
           
        }
        
    }
    /** 测试转账 */
    public function test_trans()
    {   
        $pay = Pay::getInstance('wx');
        $wechat = $pay->get_pay_obj();
                                 
        $res = $wechat->transfers('o2vrfwJTIQdk4tpPrCM3Orn-RZZk','1'*100,'2017062397975048','顺手APP提现');
        dump($res->get);
    }
    /**
     * 测试更新头衔
     */
    public function test_update_title()
    {
        $title = new Title();
        $title->update_user_title('53');
    }
    public function test_img()
    {
        $path = 'D:\htdocs\sc\Uploads\photo\43\20170714\596831adbc1ba.jpg';
        $img = new ImgHelp();
        $file = $img->app_thumb($path,'950',950);
        dump($file);
        //$img->resizeImage($path,400,400,'D:\htdocs\sc\Uploads\photo\43\20170714\S596831adbc1ba.jpg','.jpg');
    }
    public function test_con()
    {
        send_sms('18062878878','123','SMS_71305002','1');
    }
}