<?php
namespace Common\Help;
/**
 * 基础框架-助手类-父类
 * @category
 * 1、友好的数据显示信息
 * 2、CURL请求页面（封装）
 * 3、Http请求
 * @version 1.0
 * @author 陶君行
 */
class BaseHelp
{

    /**
     * 友好的数据显示信息
     * 
     * @param $data 需要展示的数据
     *            ，默认使用print_r()
     * @param $dump 是否用TP的dump打印数据
     *            0=>不使用 1=>使用
     * @param $flag 是否设置断点
     *            0=>不设置 1=>设置
     *            @time 2016-9-10
     * @author 陶君行<Silentlytao@outlook.com>
     */
    public static function show($data, $dump = 0, $flag = 0)
    {
        header("Content-type: text/html; charset=utf-8");
        /**
         * 定义样式,源于bootstarp
         */
        echo '<pre style="display: block;padding: 9.5px;margin: 44px 0 0 0;
                    font-size: 13px;line-height: 1.42857;color: #333;
                    word-break: break-all;word-wrap: break-word;background-color: #F5F5F5;
                    border: 1px solid #CCC;border-radius: 4px;">';
        /**
         * 如果是boolean或者null直接显示文字；否则print
         */
        if (is_bool($data)) {
            $show_data = $data ? 'true' : 'false';
        } elseif (is_null($data)) {
            $show_data = 'null';
        } else {
            $show_data = $dump ? var_dump($data) : print_r($data, true);
        }
        echo $show_data;
        echo '</pre>';
        if ($flag)
            die();
    }

    /**
     * 转换真实路径
     * 
     * @param $file 数据库中文件路径            
     * @example $file = 'Uploads/Member/Qrcode/1/qrcode.jpg';
     *          echo get_true_path($file);
     *          D:\htdocs\lcc\Uploads/Member/Qrcode/1/qrcode.jpg
     *          @time 2016-9-22
     * @author 陶君行<Silentlytao@outlook.com>
     */
    public static function get_true_path($file)
    {
        $file_path = getcwd() . '/' . ltrim($file, '/');
        /**
         * linux路径转换
         */
        if (DIRECTORY_SEPARATOR == '/') {
            $file_path = str_replace('\\', '/', $file_path);
        }
        return $file_path;
    }
}
?>