<?php

namespace Common\Help;
/**
 * OSS阿里云存储
 */
class OssHelp extends BaseHelp
{
   /** 实例化对象*/
    private $obj;

    const OSS_ACCESS_ID = 'LTAIuee6NSIn9qEY';
    /**阿里云oss key_id*/
    const OSS_ACCESS_KEY = 'DrggdwBw4yPDU1egHxEQ6z9JaADcs3';
    /**阿里云oss key_secret*/
                                                             // const OSS_ENDPOINT = 'oss-cn-shanghai-internal.aliyuncs.com'; // 阿里云oss endpoint
    const OSS_ENDPOINT = 'oss-cn-shanghai.aliyuncs.com';
    /**阿里云oss endpoint 外网地址*/
    const OSS_TEST_BUCKET = 'llfss';
    /**Bucket 名称*/
    const OSS_URL = 'llfss.oss-cn-shanghai.aliyuncs.com';
    /**阿里云oss对外访问地址*/
    /**
     * 构造函数
     */
    public function __construct()
    {
        vendor('Aliyunoss.autoload');
        // $this->OSS_ACCESS_ID = 'LTAIuee6NSIn9qEY';
        // $this->OSS_ACCESS_KEY = 'DrggdwBw4yPDU1egHxEQ6z9JaADcs3';
        // $this->OSS_ENDPOINT = 'oss-cn-shanghai-internal.aliyuncs.com';
        // $this->OSS_TEST_BUCKET = 'llfss';
        // $this->OSS_URL = 'llfss.oss-cn-shanghai.aliyuncs.com';
        $this->obj = new \OSS\OssClient(self::OSS_ACCESS_ID, self::OSS_ACCESS_KEY, self::OSS_ENDPOINT, false);
    }

    /**
     * 上传文件到oss并删除本地文件
     * 
     * @param string $path
     *            文件路径
     * @param string $oss_path oss文件路径
     * @return bollear 是否上传
     */
    function oss_upload($path, $oss_path = '')
    {
        if (empty($path)) {
            return false;
        }
        if (! file_exists($path)) {
            write_debug($path, '需要上传到OSS的文件不存在');
        }
        
        /* 先统一去除左侧的.或者 再添加 */
        if (empty($oss_path)) {
            $oss_path = ltrim($path, './');
        }
        $path = './' . $path;
        /* 上传到oss */
        try {
            $this->obj->uploadFile(self::OSS_TEST_BUCKET, $oss_path, $path);
            // unlink($path);
        } catch (OssException $e) {
            /* 如需上传到oss后 自动删除本地的文件 则删除下面的注释 */
            // unlink($path);
            write_debug($e->getMessage(), 'oss文件上传失败');
            return false;
        }
        write_debug(self::OSS_ENDPOINT . '' . $path, 'oss文件上传地址');
        return true;
    }

    /**
     * 获取完整网络连接
     * 
     * @param string $path
     *            文件路径不带 './'
     * @return string http连接
     */
    function oss_url($path)
    {
        // 如果是空；返回空
        if (empty($path)) {
            return '';
        }
        // 如果已经有http直接返回
        if (strpos($path, 'http://') !== false) {
            return $path;
        }
        // 获取bucket
        $bucket = self::OSS_URL;
        return 'http://' . $bucket . '/' . $path;
    }

    /**
     * 删除oss上指定文件
     * 
     * @param string $path
     *            文件路径 例如删除 /Public/README.md文件 传Public/README.md 即可
     */
    function oss_del($path)
    {
        if (empty($path))
            return false;
        
        try {
            $this->obj->deleteObject(self::OSS_TEST_BUCKET, ltrim($path, './'));
        } catch (OssException $e) {
            write_debug($path, 'oss文件删除成功');
            return false;
        }
        write_debug($path, 'oss文件删除失败');
        return true;
    }

}

?>