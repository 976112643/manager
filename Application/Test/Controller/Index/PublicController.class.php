<?php
namespace Test\Controller\Index;

use Common\Controller\CommonController;
use Common\Help\ImgHelp;
use Common\Help\HttpHelp;

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
        $info = get_no_del('app_version', 'id desc');
        if ($info) {
            /** 生成下载二维码*/
            $text = reset($info)['version'];
            $url = U('down', array(), true, true);
            $img = $this->create_down_qrcode($text, $url);
            $this->assign('app_img', $img);
            $this->display('Common/down');
        }
    }

    /**
     * 下载商家APK
     */
    public function down()
    {
        $info = get_no_del('app_version', 'id desc');
        $info = reset($info);
        $http = new HttpHelp();
        $http->download($info['path'], $info['name'] . $info['version'] . '.apk');
    }

    /**
     * 生成下载二维码
     * @param  [string] $text    [生成的文字]
     * @param  [string] $url     [生成二维码的链接]
     * @return [string]          [返回的文件路径]
     */
    protected function create_down_qrcode($text, $url)
    {
        $save_path = 'Uploads/APP/';
        $file = $save_path . 'down.jpg';
        if (is_file($file)) {
            return $file;
        }
        $img = new ImgHelp();
        mk_dir($save_path);
        $img->create_text_qrcode($text, $url, $file);
        return $file;
    }
}


?>