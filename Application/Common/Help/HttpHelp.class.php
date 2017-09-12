<?php

namespace Common\Help;

use Org\Net\Http;
/**
 * 基础框架-助手类-网络请求类
 */
class HttpHelp extends Http
{

    /**
     * CURL请求页面
     * @param unknown $url
     * @param unknown $params
     * @param string $method
     * @param array $header
     * @param string $multi
     * @return mixed
     */
    static public function curl_info($url, $params, $method = 'GET', $header = array(), $multi = false)
    {
        /**
         * 检测是否是完成URL
         */
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            // $url = U($url,'',true,true);
            $temp = 'http://127.0.0.1';
            $url = $temp . U($url);
        }
        $data = json_decode(self::http($url, $params, $method, $header, $multi), true);
        return $data;
    }

    /**
     * 发送HTTP请求方法，目前只支持CURL发送请求
     * @param string $url     请求URL
     * @param string $params  请求参数
     * @param string $method  请求方式
     * @param array $header   请求头
     * @param string $multi   请求文件
     * @throws Exception
     * @return mixed
     */
    static public function http($url, $params, $method = 'GET', $header = array(), $multi = false)
    {
        $opts = array(
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => $header
        );
        /**
         * 根据请求类型设置特定参数
         */
        switch (strtoupper($method)) {
            case 'GET':
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                break;
            case 'POST':
                // 判断是否传输文件
                $params = $multi ? $params : http_build_query($params);
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            case 'DEL':
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;
            default:
                throw new Exception('不支持的请求方式！');
        }
        /**
         * 初始化并执行curl请求
         */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error)
            throw new Exception('请求发生错误：' . $error);
        return $data;
    }

    /**
     * 以get方式提交请求
     * 
     * @param
     *            $url
     * @return bool|mixed
     */
    static public function httpGet($url)
    {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

    /**
     * 以post方式提交请求
     * 
     * @param string $url            
     * @param array|string $postdata            
     * @return bool|mixed
     */
    static public function httpPost($url, $postdata)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        if (is_array($postdata)) {
            foreach ($postdata as &$value) {
                if (is_string($value) && stripos($value, '@') === 0 && class_exists('CURLFile', FALSE)) {
                    $value = new CURLFile(realpath(trim($value, '@')));
                }
            }
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $data = curl_exec($ch);
        curl_close($ch);
        if ($data) {
            return $data;
        }
        return false;
    }

    /**
     * 使用证书，以post方式提交xml到对应的接口url
     * 
     * @param string $url
     *            POST提交的内容
     * @param array $postdata
     *            请求的地址
     * @param string $ssl_cer
     *            证书Cer路径 | 证书内容
     * @param string $ssl_key
     *            证书Key路径 | 证书内容
     * @param int $second
     *            设置请求超时时间
     * @return bool|mixed
     */
    static public function httpsPost($url, $postdata, $ssl_cer = null, $ssl_key = null, $second = 30)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        /* 要求结果为字符串且输出到屏幕上 */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        /* 设置证书 */
        if (! is_null($ssl_cer) && file_exists($ssl_cer) && is_file($ssl_cer)) {
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, $ssl_cer);
        }
        if (! is_null($ssl_key) && file_exists($ssl_key) && is_file($ssl_key)) {
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, $ssl_key);
        }
        curl_setopt($ch, CURLOPT_POST, true);
        if (is_array($postdata)) {
            foreach ($postdata as &$data) {
                if (is_string($data) && stripos($data, '@') === 0 && class_exists('CURLFile', FALSE)) {
                    $data = new CURLFile(realpath(trim($data, '@')));
                }
            }
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }
    /**
     * get远程获取页面源码
     * @param   $[url] [远程URL]
     */
    public function http_code($url)
    {
        $UserAgent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; .NET CLR 3.5.21022; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);  //0表示不输出Header，1表示输出
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_USERAGENT, $UserAgent);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        $data = curl_exec($curl);
        curl_close($curl);
        if ($data) {
            return $data;
        } else {
            return false;
        }
    }
    /**
     * 采集远程URL上的所有图片
     * @param  [type] $url      [远程URL]
     * @param  [type] $filename [保存文件名]
     * @return [type]           [description]
     */
    public function colle_img($url,$filename)
    {
        if(!$url) return 'Uploads/Recommend/head_image.jpg';
        $hander = curl_init();
        curl_setopt($hander,CURLOPT_URL,$url);
        curl_setopt($hander,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($hander,CURLOPT_RETURNTRANSFER,true);//以数据流的方式返回数据,当为false是直接显示出来
        curl_setopt($hander,CURLOPT_TIMEOUT,60);
        $str = curl_exec($hander);
        curl_close($hander);
        file_put_contents($filename, $str);
        if($str==''){
          return 'Uploads/Recommend/head_image.jpg';
        }
        return  $filename;
    }
    
}
?>