<?php 
namespace Api\Controller\Common;

use Api\Controller\Base\BaseController;

/**
 * 【通用】广告banner图片，貌似没有什么用了
 */
class BannerController extends BaseController
{
    /** 广告表*/
    protected $table = 'banner';
    
    /**
     * 获取首页广告-用于接口
     */
    public function get_banner()
    {
        $data = $this->get_true_banner();

        if(count($data)){
            $this->set_success('ok',$data);
        }else{
            $this->set_error('暂无广告');
        }
    }

    /**
     * 测试添加方法
     */
    public function add()
    {
        
    }
    /**
     * 获取广告banner,用于外部类调用
     * @param string $[type] [广告类型]  10 用户端 20 工人端
     */
    public function get_true_banner($type = '10')
    {
        /** 获取默认缓存广告 */
        $res = $this->get_cache_banner($type);
        /** 判断广告是否过期*/
        $now = date('Y-m-d 00:00:00');
        foreach ($res as $key => $value) {
            $flag  = $this->is_expired($value,$now);
            if($flag === false){
                unset($res[$key]);
            }else{
                $res[$key] = $flag;
            }
        }
        return array_values($res);
    }
    /**
     * 获取广告缓存内容
     * @param string $[type] [广告类型]
     */
    protected function get_cache_banner($type)
    {
        /** 读取缓存*/
        $cache = 'banner_'.$type;
        if(F($cache)){
            $res = F($cache);
        }else{
            $field = 'id,type,title,content,save_path,url,sort,end_time';
            $res = get_result($this->table, array('app_type' => $type), 'sort desc,id asc', $field);
            F($cache,$res);
        }
        return $res;
    }
    /**
     * 判断广告是否过期
     * @param array $[row] [广告数据]
     * @param string $[time] [过期时间]
     */
    protected function is_expired($row,$time)
    {
        if($row['end_time'] < $time){
            return false;
        }
        $row['save_path'] = file_url($row['save_path']);
        if (!preg_match("/^(http|ftp|https):/", $row['url']) && $row['url']){
           $row['url'] = 'http://'.$row['url'];
        }
        unset($row['end_time']);
        unset($row['sort']);
        unset($row['content']);
        return $row;
    }
}

?>