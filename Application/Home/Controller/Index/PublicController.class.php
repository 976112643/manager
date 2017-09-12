<?php 
namespace Home\Controller\Index;

use Common\Controller\CommonController;
use Common\Help\ImgHelp;
use Common\Help\HttpHelp;
use Common\Help\BaseHelp;
/**
 * 前台模块不受限方法
 */
class PublicController extends CommonController
{
    /**
     * 下载商家端APK
     */
    public function index()
    {
        $info = $this->get_app_data();
        if($info){
            $img = $this->create_down_qrcode();
            $this->assign('app_member_img',$img);
            $this->display();
        }
    }
    /**
     * 下载商家APK
     */
    public function down()
    {
        $app_type = I('app_type','1','int');
        if($app_type != '1' && $app_type != '2')    $this->error('不存在的应用');
        $res = $this->get_app_data();
        /** 判断设备类型 */
        $agent = get_agent();
        if($agent == 'android'){
            $info = $res[$app_type]['10'];
            header("location:" . file_url($info['path']));exit;
            $http = new HttpHelp();
            $http->download($info['path'],$info['name'].$info['version'].'.apk');
        }else if($agent == 'iphone' || $agent =='ipad'){
            $info = $res[$app_type]['20'];
            header("location:" . $info['url']);
        }else if($agent == 'weixin'){
            $this->error('请使用浏览器打开扫码');
        }else{
            $this->error('不支持的设备类型');
        }

        
    }
    /**
     * 获取用户端、工人端应用数据
     */
    protected function get_app_data()
    {
        $res = get_no_del('app_version','id desc');
        if(!$res) return array();
        /** 获取用户端、工人端数据 */
        $data = array();
        foreach ($res as $key => $value) {
            $data[$value['app_type']][$value['type']] = $value;        
        }
        return $data;
    }
    /**
     * 生成下载二维码
     * @return [string]          [返回的文件路径]
     */
    protected function create_down_qrcode()
    {
        $save_path = 'Uploads/APP/';
        $file = $save_path.'down.jpg';
        if(is_file($file)){
            return file_url($file);
        }
        $base = new BaseHelp();
        $img = new ImgHelp();
        /**存放路径*/
        $path = $base->get_true_path( $save_path );
        /**创建人员目录*/
        $new_path = mk_dir($path);
        if($new_path == false) $this->error ( "目录创建失败" );

        $default_path = $base->get_true_path( C('APP_LOGO') );
        /**存储背景二维码图片*/
        $qrcode = $path.'qrcode.png';
        if( ! file_exists($qrcode)){
            $url = U('/Home/Index/Public/down',array(),true,true);
            /**生成背景图片*/
            $config = array(
                'outfile'  =>$qrcode,
                'level' =>'H',
                'size' =>4
            );
            $img->create_qrcode($url,$config);
        }
        /**生成带LOGO的二维码*/
        if(! file_exists($file)){
            $img->creatr_logo_qrcode($qrcode,$default_path,$file);
        }
        return file_url($file);
    }
    /** 
     * 关于我们的html展示
     */
    public function about()
    {
        $data['data'] = C('ABOUTUS');
        $this->assign($data);
        $this->display();
    }
}


?>