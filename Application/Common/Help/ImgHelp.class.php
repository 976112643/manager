<?php

namespace Common\Help;

use Think\Image;
use Endroid\QrCode\QrCode;
/**
 * 基础框架-助手类-图像处理
 */
class ImgHelp extends Image
{

    /**
     * desription 压缩图片
     * 
     * @param sting $imgsrc
     *            图片路径
     * @param string $imgdst
     *            压缩后保存路径
     */
    public function compressed_image($imgsrc, $imgdst)
    {
        list ($width, $height, $type) = getimagesize($imgsrc);
        $scale = $width / $height;
        if ($width > 1000 && $height > 1000) {
            $new_height = 1000 / $scale;
            $new_width = $new_height * $scale;
        }
        $image_wp = imagecreatetruecolor($new_width, $new_height);
        switch ($type) {
            case 1:
                $giftype = $this->check_gifcartoon($imgsrc);
                if ($giftype) {
                    header('Content-Type:image/gif');
                    $image = imagecreatefromgif($imgsrc);
                }
                break;
            case 2:
                header('Content-Type:image/jpeg');
                $image = imagecreatefromjpeg($imgsrc);
                break;
            case 3:
                header('Content-Type:image/png');
                $image = imagecreatefrompng($imgsrc);
                break;
        }
        if ($image_wp) {
            imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            // 75代表的是质量、压缩图片容量大小
            imagejpeg($image_wp, $imgdst, 30);
            imagedestroy($image_wp);
        }
    }

    /**
     * desription 判断是否gif动画
     * 
     * @param sting $image_file图片路径            
     * @return boolean t 是 f 否
     */
    public function check_gifcartoon($image_file)
    {
        $fp = fopen($image_file, 'rb');
        $image_head = fread($fp, 1024);
        fclose($fp);
        return preg_match("/" . chr(0x21) . chr(0xff) . chr(0x0b) . 'NETSCAPE2.0' . "/", $image_head) ? false : true;
    }

    /**
     * 解决手机上传竖向图片横向显示问题 需要开启拓展 php_exif
     * @param string $img_src 图像路径
     */
    public function rever_img($img_src)
    {
        if (function_exists('exif_read_data')) {
            $filename = $info['download']['save_path'];
            $source = imagecreatefromjpeg($filename);
            $exif = exif_read_data($filename);
            if (! empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 8:
                        $source = imagerotate($source, 90, 0);
                        break;
                    case 3:
                        $source = imagerotate($source, 180, 0);
                        break;
                    case 6:
                        $source = imagerotate($source, - 90, 0);
                        break;
                }
            }
            imagejpeg($source, $filename);
        }
    }

    /**
     * 传入图片地址,得到图片的Base64编码
     * 
     * @param [string] $img_file
     *            [图片路径 Uploads/avate/2.jpg]
     * @param boolean $flag 是否开启去除头部data
     * @return [string] [Base64编码]
     */
    public static function img_base64($img_file, $flag = true)
    {
        $img_base64 = '';
        $app_img_file = self::get_true_path($img_file); // 组合出真实的绝对路径
        $fp = fopen($app_img_file, "r"); // 图片是否可读权限
        if ($fp) {
            $img_base64 = chunk_split(base64_encode(fread($fp, filesize($app_img_file)))); // base64编码
            fclose($fp);
        }
        /**
         * 如果为true,就给出base64图片的头信息
         */
        if ($flag) {
            $img_info = getimagesize($app_img_file); // 取得图片的大小，类型等
            switch ($img_info[2]) { // 判读图片类型
                case 1:
                    $img_type = "gif";
                    break;
                case 2:
                    $img_type = "jpg";
                    break;
                case 3:
                    $img_type = "png";
                    break;
                default:
                    $img_type = 'jpg';
                    break;
            }
            $img_base64 = 'data:image/' . $img_type . ';base64,' . $img_base64;
        }
        return $img_base64; // 返回图片的base64
    }

    /**
     * 传入图片base64编码,返回图片内容
     * @param string $data base图像编码
     */
    public static function base64_to_img($data)
    {
        // 保存base64字符串为图片
        // 匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $data, $result)) {
            $res['type'] = $result[2]; // 图片类型
            $res['img'] = base64_decode(str_replace($result[1], '', $data));
        }
        /**
         * 后续可以使用 file_put_contents($new_path, $res['img']); 保存图片
         */
        return $res;
    }

    /**
     * 生成二维码
     * 
     * @param string $url
     *            url连接
     * @param array $config
     *            配置
     *            <说明> $config = array(
     *            'outfile' => false, ##不生成文件；只将二维码图片返回；否则需要给出存放生成二维码图片的路径；
     *            'level' => L , ##这个参数可传递的值分别是L(QR_ECLEVEL_L，7%)、M(QR_ECLEVEL_M，15%)、Q(QR_ECLEVEL_Q，25%)、H(QR_ECLEVEL_H，30%)；这个参数控制二维码容错率；不同的参数表示二维码可被覆盖的区域百分比。利用二维维码的容错率；我们可以将头像放置在生成的二维码图片任何区域；
     *            'size' => 4, ##控制生成图片的大小；默认为4;
     *            'margin' => 4, ##控制生成二维码的空白区域大小;
     *            'saveandprint' => false, ##保存二维码图片并显示出来；否则 $outfile必须传递图片路径;
     *            'back_color' => '0xFFFFFF', ##背景颜色 16进制;
     *            'fore_color' => '0x000000', ##绘制二维码的颜色 16进制;
     *            );
     *            @time 2016-8-22
     * @author 陶君行<Silentlytao@outlook.com>
     */
    public function create_qrcode($url, $config)
    {
        Vendor('phpqrcode.phpqrcode');
        /**
         * png($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4,
         * $saveandprint=false, $back_color = 0xFFFFFF, $fore_color = 0x000000)
         */
        
        $outfile = $config['outfile'] ? $config['outfile'] : false;
        $level = $config['level'] ? $config['level'] : L;
        $size = $config['size'] ? $config['size'] : 4;
        $margin = $config['margin'] ? $config['margin'] : 4;
        $saveandprint = $config['saveandprint'] ? $config['saveandprint'] : false;
        $back_color = $config['back_color'] ? $config['back_color'] : 0xFFFFFF;
        $fore_color = $config['fore_color'] ? $config['fore_color'] : 0x000000;
        
        \QRcode::png($url, $outfile, $level, $size, $margin, $saveandprint, $back_color, $fore_color);
    }

    /**
     * 制作带LOGO的二维码
     * 
     * @param $[QR] [<背景二维码>]            
     * @param $[headimg] [<LOGO图片>]            
     * @param $[logo_path] [<存储地址>]
     *            @time 2016-8-22
     * @author 陶君行<Silentlytao@outlook.com>
     */
    public function creatr_logo_qrcode($QR, $headimg, $logo_path)
    {
        if ($logo_path) {
            $QR = imagecreatefromstring(file_get_contents($QR));
            $logo = imagecreatefromstring(file_get_contents($headimg));
            $QR_width = imagesx($QR); // 二维码图片宽度
            $QR_height = imagesy($QR); // 二维码图片高度
            $logo_width = imagesx($logo); // logo图片宽度
            $logo_height = imagesy($logo); // logo图片高度
            $logo_qr_width = $QR_width / 5;
            $scale = $logo_width / $logo_qr_width;
            $logo_qr_height = $logo_height / $scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;
            // 重新组合图片并调整大小
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
        }
        imagepng($QR, $logo_path);
    }

    /**
     * /**
     * 生成带标签文字的二维码
     * @param string $text 文字
     * @param string $url 二维码URL
     * @param string $save_path  保存路径
     */
    public function create_text_qrcode($text = '', $url = '', $save_path = '')
    {
        vendor('Qrcode.QrCode');
        $qrCode = new QrCode();
        $qrCode->setText($url)
            ->setSize(300)
            ->setPadding(10)
            ->setErrorCorrection('high')
            ->setForegroundColor([
            'r' => 0,
            'g' => 0,
            'b' => 0,
            'a' => 0
        ])
            ->setBackgroundColor([
            'r' => 255,
            'g' => 255,
            'b' => 255,
            'a' => 0
        ])
            ->setLabel($text)
            ->setLabelFontSize(16)
            ->setImageType(QrCode::IMAGE_TYPE_PNG);
        // save it to a file
        $qrCode->save($save_path);
    }
    /**
     * 生成APP缩略图
     */
    public function app_thumb($image,$width,$height,$prefix = 'S',$default_pic)
    {
        $default_pic = $default_pic ? $default_pic : C('TMPL_PARSE_STRING')['__STATIC__'] . "/img/default/list.jpg";
        if (! in_array($prefix, [
            'L',
            'M',
            'S'
        ])) {
            return "缩略图前缀只能使用L、M、S";
        }
        if (file_exists($image)) {
            $name = basename($image);
            $new_image = dirname($image) . '/' . $prefix . $name;
            if (! file_exists($new_image) && file_exists($image)) {
                list ($width_orig, $height_orig) = getimagesize($image);
                if ($width_orig != $width || $height_orig != $height) {
                    
                    $this->resizeImage($image,$width,$height,$new_image);
                } else {
                    copy($image, $new_image);
                }
            }
            return $new_image;
        } else {
            $name = basename($image);
            $new_image_default = dirname($image) . '/' . $prefix . $name;
            if (! file_exists($new_image_default) && file_exists($default_pic)) {
                list ($width_orig, $height_orig) = getimagesize($default_pic);
                if ($width_orig != $width || $height_orig != $height) {
                    $this->resizeImage($default_pic,$width,$height,$new_image_default);
                } else {
                    copy($default_pic, $new_image_default);
                }
            }
            return $new_image_default;
        }
    }
    /**
     * 等比缩放
     * @param  [type] $im        [description]
     * @param  [type] $maxwidth  [description]
     * @param  [type] $maxheight [description]
     * @param  [type] $name      [description]
     * @return [type]            [description]
     */
    public function resizeImage($im,$maxwidth,$maxheight,$name)
    {
        $im = imagecreatefromjpeg($im);
        $pic_width = imagesx($im);
        $pic_height = imagesy($im);
        if(($maxwidth && $pic_width > $maxwidth) || ($maxheight && $pic_height > $maxheight))
        {
            if($maxwidth && $pic_width>$maxwidth)
            {
                $widthratio = $maxwidth/$pic_width;
                $resizewidth_tag = true;
            }
            if($maxheight && $pic_height>$maxheight)
            {
                $heightratio = $maxheight/$pic_height;
                $resizeheight_tag = true;
            }
            if($resizewidth_tag && $resizeheight_tag)
            {
                if($widthratio<$heightratio)
                    $ratio = $widthratio;
                else
                    $ratio = $heightratio;
            }
            if($resizewidth_tag && !$resizeheight_tag)
                $ratio = $widthratio;
            if($resizeheight_tag && !$resizewidth_tag)
                $ratio = $heightratio;
            $newwidth = $pic_width * $ratio;
            $newheight = $pic_height * $ratio;
            if(function_exists("imagecopyresampled"))
            {
                $newim = imagecreatetruecolor($newwidth,$newheight);
               imagecopyresampled($newim,$im,0,0,0,0,$newwidth,$newheight,$pic_width,$pic_height);
            }
            else
            {
                $newim = imagecreate($newwidth,$newheight);
               imagecopyresized($newim,$im,0,0,0,0,$newwidth,$newheight,$pic_width,$pic_height);
            }
            imagejpeg($newim,$name);
            imagedestroy($newim);
        }
        else
        {
            imagejpeg($im,$name);
        }          
    }
}
?>