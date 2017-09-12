<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/9
 * Time: 21:38
 */
namespace Common\Plugin;

use Common\Help\RedisHelp;
use Common\Help\ImgHelp;
/**
 * 用户相册管理类
 * @package Common\Plugin
 */
class PersonPhoto extends Base
{
    /**
     * 子类继承
     */
    protected function __init()
    {
        $this->table = 'member_photo';
    }
    /**
     * 我的相册列表
     */
    public function my_photo($uid)
    {
        $cache = 'member_photo:'.$uid;
        $redis = RedisHelp::getInstance();
        $res = $redis->get($cache);
        if( !$res ){
            $map = array(
                'uid' =>$uid
            );
            $field = 'id,image,addtime';
            /** 获取用户相册信息 */
            $model = M($this->table);
            $res = get_result($model,$map,'addtime desc,id asc',$field);
            $img = new ImgHelp();
            if( $res ){
                foreach ( $res as $k => $v){
                    $res[$k]['image'] = show_member_head_img('',$v['image']);
                    $res[$k]['thumb_image'] = show_member_head_img('',$img->app_thumb($v['image'],'950','950'));
                    $res[$k]['add_time'] = date('Y-m-d H:i',$v['addtime']);
                }
            }
            $redis->set($cache,$res);
        }
        return $res;
    }
    /**
     * 我的展示相册
     */
    public function my_photo_views($uid)
    {
        try{
            /** 获取缓存中相册数据 */
            $cache = 'member_photo:'.$uid;
            $redis = RedisHelp::getInstance();
            $res = $redis->get($cache);
            if( !$res ){
                throw new \Exception("error", 1);
            }
            $res = array_chunk($res,9);
            return $res['0'];
        }catch(\Exception $e){
            /** 从数据库中获取9条 */
            $map = array(
                'uid' =>$uid
            );
            $field = 'id,image,addtime';
            /** 获取用户相册信息 */
            $model = M($this->table);
            $res = get_result($model,$map,'addtime desc,id asc',$field,9);
            $img = new ImgHelp();
            if( $res ){
                foreach ( $res as $k => $v){
                    $res[$k]['image'] = show_member_head_img('',$v['image']);
                    $res[$k]['thumb_image'] = show_member_head_img('',$img->app_thumb($v['image'],'950','950'));
                    $res[$k]['add_time'] = date('Y-m-d H:i',$v['addtime']);
                }
            }
            return $res;
        }
    }
    /**
     * 添加相册
     */
    public function add_photo( $uid )
    {
        /** 添加进数据库 */
        $filed = 'photo';
        $save_path = 'Uploads/photo/'. $uid .'/';
        $info = api_upload_picture($filed,$save_path);
        if( !is_array($info) ) return $info;
        try{
            if( $info['0'] ){
                $img = $info;
            }else{
                $img = array($info);
            }
            $data = array();
            foreach($img as $file){  
                $data[] = array(
                    'uid' =>$uid,
                    'image'      =>$file['savepath'].$file['savename'],
                    'addtime'=>NOW_TIME,
                );
            }
            $res = M($this->table)->addAll($data);
            /** 清理Redis */
            $this->clear_user_photo($uid);
            return $res;
        }catch (\Exception $e){
            return '系统异常';
        }
        
    }
    /**
     * 删除相册
     */
    public function del_photo( $uid ,$ids )
    {
        if( is_array($ids) ){
            $map['id'] = array('in',$ids);
        }else{
            $map['id'] = $ids;
        }
        $info = get_result($this->table,$map);
        foreach ($info as $key => $value) {
            del_thumb($value['image']);
        }
        try{
            delete_data($this->table,$map);
            /** 清理Redis */
            $this->clear_user_photo($uid);
            return 1;
        }catch (\Exception $e){
            return '删除失败';
        }
    }
    /**
     * 清理Redis缓存
     */
    protected function clear_user_photo( $uid )
    {
        $cache = 'member_photo:'.$uid;
        $redis = RedisHelp::getInstance();
        $redis->del($cache);
    }
}