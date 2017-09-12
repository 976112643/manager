<?php 
namespace Api\Controller\Common;

use Api\Controller\Base\BaseController;
use Common\Help\Geohash;
use Common\Help\DateHelp;

/**
 * 【通用】公共模块控制器
 * @author Administrator
 *
 */
class IndexController extends BaseController
{
    public function home()
    {
        /** 获取banner图片 */
        $banner = new BannerController();
        $data['banner'] = $banner->get_true_banner();
        /** 获取任务列表 */
        $data['list'] = $this->get_list();
        $this->set_success('ok',$data);
    }
    /**
     * 获取任务列表
     * @return [type] [description]
     */
    protected function get_list()
    {
        $map = $this->get_map();
        $get = I('get.','','trim');
        $lng = $get['longitude']?$get['longitude']:'114.39314';
        $lat = $get['latitude']?$get['latitude']:'30.510008';

        $radius = 6378.138; // 地球半径 KM
        $dist = 'ROUND('. $radius .'*2*ASIN(SQRT(POW(SIN(('
                .$lat.'*PI()/180-latitude*PI()/180)/2),2)+COS('
                .$lat.'*PI()/180)*COS(latitude*PI()/180)*POW(SIN(('
                .$lng.'*PI()/180-longitude*PI()/180)/2),2)))*1000) AS distance';

        $field='id,'.$dist.',head_img,member_nickname,money_total,status,door_time,longitude,latitude,name,type,start_rating,member_id,add_time,detail_title,description,is_img';
        $sort= 'distance asc,a.add_time desc,id desc';
        $data = $this->page(D('DemandOrderView'),$map,$sort,$field);
        if( $data['list'] ){
            $geo = new Geohash();
            $date = new DateHelp();
            $sortdistance = array();
            $idsort = array();
            /** 根据当前定位的 */
            $order_id = array();
            foreach ($data['list'] as $key => $v) {
                $data['list'][$key]['description'] = $this->remove_sensitive($v['description']);
                $data['list'][$key]['detail_title'] = $this->remove_sensitive($v['detail_title']);
                /** 计算距离 */
                $distance = $geo->getDistance(I('latitude'),I('longitude'),$v['latitude'],$v['longitude']);
                $data['list'][$key]['distance'] = $geo->deal($distance);
                /** 计算时间 */
                $data['list'][$key]['last_time'] = $date->time_diff($v['add_time']);
                $data['list'][$key]['head_img'] = file_url($v['head_img']);
                //排序列
                $sortdistance[$key] = $distance;
                $idsort[$key] = $v['id'];
                $field = 'id,money_total,status,distance,last_time,head_img,member_nickname,member_id,start_rating,name,type,detail_title,description,is_img';
                $data['list'][$key] = $this->pick_char($data['list'][$key],$field);
                /** 查询是否有图片 */
                if($v['is_img']){
                    $order_id[] = $v['id'];
                }
            }
            if($order_id){
                $img = get_result('order_image',array('order_id'=>array('in',$order_id)),'','order_id,image');
                /** 图片转成全路径 */
                $img_class = new \Common\Help\ImgHelp();
                array_walk($img, function(&$a) use($img_class){ 
                    $_img = $a['image'];
                    $a['image'] = file_url($_img);
                    $a['thumb_image'] = show_member_head_img('',$img_class->app_thumb($_img,'950','950'));
                });
            }
            /** 合并原数据组 */
            foreach ($data['list'] as $k => $v) {
                $order_img = array();
                foreach ($img as $key => $value) {
                    if($value['order_id'] == $v['id']){
                        unset($value['order_id']);
                        $order_img[] = $value;
                    }
                }
                $data['list'][$k]['order_img'] = $order_img;
            }
            
            /** 重新排序 */
            array_multisort($sortdistance,SORT_ASC,$idsort,SORT_DESC,$data['list']);
        }else{
            $data['list'] = array();
        }


        return $data['list'];
    }
    /**
     * 获取搜索条件
     * @return [type] [description]
     */
    protected function get_map()
    {
        $map = array();
        $get = I('get.','','trim');
        $lng = $get['longitude']?$get['longitude']:'114.39314';
        $lat = $get['latitude']?$get['latitude']:'30.510008';
        $geohash = new Geohash();
        $n_geohash = $geohash->encode($lat,$lng);
        $n = I('p','1','int');
        $like_geohash = substr($n_geohash, 0,$n);
        //$map['geo_hash'] =  array('like',$like_geohash.'%');
        // $km=I('distance','10','int');
        // if($km>0&&$lat&&$lng){
        //     $range = 180 / pi() * $km / 6370; //里面的 1 就代表搜索 1km 之内，单位km
        //     $lngR = $range / cos($lat * pi() / 180);
        //     $maxLat = $lat + $range;//最大纬度
        //     $minLat = $lat - $range;//最小纬度
        //     $maxLng = $lng + $lngR;//最大经度
        //     $minLng = $lng - $lngR;//最小经度
        //     $map['longitude'] = array('between',array($minLng,$maxLng)); //经度值
        //     $map['latitude'] = array('between',array($minLat,$maxLat)); //纬度值
        // }
        $map['status'] = array('in',array('20','22'));
        if( $get['user_key'] && $get['keywords']){
            
            /** 添加入历史搜索中 */
            $this->is_auth();
            $this->add_keyword($this->_id,$get['keywords']);
        }
        if( $get['keywords'] ) {
            $map['detail_title | description'] = array('like','%'.$get['keywords'].'%');
        }
        /**
         * 到期时间大于当前时间的任务
         */
        $map['door_time'] = array('egt',date('Y-m-d H:i:s'));
        return $map;

    }
    /**
     * 1分钟刷一次地理位置
     * @author 鲍海
     * @time 2017-03-28
     */
    public function set_member_address(){
        $member_id = $this->_id;    
        $DemandRedis = new DemandRedis();
        
        $address['longitude'] = I('post.longitude');//用户经度
        $address['latitude'] = I('post.latitude');//用户纬度
        if(!I('post.longitude')){$this->set_error('经度必须');}
        if(!I('post.latitude')){$this->set_error('纬度必须');}
        
        $flag = $DemandRedis->join_local($member_id,$address);
        
        $this->set_success('上报地址成功');
    }
    /**
     * 历史搜索关键字
     */
    public function history_keywords()
    {
        $this->is_auth();
        $map = array(
            'uid' =>$this->_id
        );
        $res = get_result('member_search_history',$map,'id desc','id,keyword','10');
        if( $res ){
            $this->set_success('ok',$res);
        }else{
            $this->set_error('暂无历史记录');
        }
    }
    /**
     * 加入到历史关键字
     */
    protected function add_keyword($uid,$keyword)
    {
        $map = array(
            'uid' =>$uid,
            'keyword' =>$keyword
        );
        $info = get_info('member_search_history',$map,'id');
        if( $info['id'] == '' ){
            /** 如果不存在就添加 */
            $data = array(
                'uid' =>$uid,
                'keyword' =>$keyword
            );
            update_data('member_search_history',[],[],$data);
        }
    }
    /**
     * 删除历史搜索关键字
     */
    public function del_keyword()
    {
        $this->is_auth();
        $map = array(
            'uid' =>$this->_id,
        );
        $res = update_data('member_search_history',[],$map,array('is_hid' =>'1'));
        if( $res ){
            $this->set_success('删除成功',$res);
        }else{
            $this->set_error('删除失败');
        }
    }
    /**
     * 关于我们
     */
    public function about()
    {
        $data = array(
            'about' =>C('ABOUTUS'),
            'about_copy'=>C('COPY_RIGHT'),
            'about_gz' =>C('ABOUT_GZ'),
            'about_url' =>C('ABOUT_URL'),
            'about_company'=>C('ABOUT_COMPANY')
        );
        $this->set_success('ok',$data);
    }
    /**
     * 获取客服微信
     */
    public function kf()
    {
        $pic = thumb(C('WEIXIN_SERVE_COVER'),'150','150');
        //$pic = str_replace(__ROOT__.'/', '', $pic);
        $data = array(
            'kf' =>C('WEIXIN_SERVE'),
            'kf_cover' =>file_url($pic)
        );
        $this->set_success('ok',$data);
    }
    /**
     * 公共，第三方登录APP验证
     */
    public function ios_is_online()
    {
        $data = array(
            'is_online' =>C('IOS_IS_LOCK')  //是否是上线审核状态,如果是 1 则关闭第三方登录
        );
        $this->set_success('ok',$data);
    }
}




?>