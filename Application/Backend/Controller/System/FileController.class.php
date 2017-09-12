<?php
/**
 * 文件操作相关控制器
 * @package 
 * @author 王淳熙 
 */
namespace Backend\Controller\System;

/**
 * 文件操作相关控制器类
 * 
 * @package
 *
 * @author 王淳熙
 */
class FileController extends IndexController
{

    /**
     * 功能：查询指定文件夹下的所有文件（初始化默认为Runtime）
     *
     * @author 王淳熙
     */
    public function index()
    {
        $info = array();
        /**
         * 获取参数$path
         * $path :在前端是要访问的文件或文件夹名
         * $path :在后台是要打开的路径
         */
        $path = I('get.path');
        /**
         * 获取前端参数$path_one
         * $path_one:是前台传递过来的当前的路径
         */
        $path_one = I("path_one");
        /**
         * 判断是否是POST请求
         */
        if (IS_POST) {
            /**
             * 判断前台传递的路径是否为空
             */
            if (empty($path_one)) {
                /**
                 * 为空就访问初始化文件夹
                 */
                $path = RUNTIME_PATH;
            } else {
                /**
                 * 不为空进行路径和文件夹拼接
                 */
                /**
                 * 判断文件名是否是..
                 */
                if ($path == "..") {
                    /**
                     * 如果为..就产生返回上一层路径
                     */
                    $sum = strripos($path_one, DIRECTORY_SEPARATOR);
                    $path = substr($path_one, 0, $sum);
                } else {
                    /**
                     * 如果不为..就进行路径和文件名进行拼接
                     */
                    $path = $path_one . DIRECTORY_SEPARATOR . $path;
                }
            }
            // 获取本文件目录的文件夹地址
            $filesnames = scandir($path);
            /**
             * 判断是否为根目录
             */
            if ($path == RUNTIME_PATH) {
                /**
                 * 为根目录文件销毁数组指定参数
                 */
                unset($filesnames[0]);
                unset($filesnames[1]);
            } else {
                /**
                 * 不为根目录文件销毁数组指定参数
                 */
                unset($filesnames[0]);
                $previous[0] = $filesnames[1];
                unset($filesnames[1]);
            }
            /**
             * 获取所有文件的详细信息
             */
            foreach ($filesnames as $value) {
                /**
                 * 判断是否为文件
                 */
                if (strpos($value, '.')) {
                    /**
                     * 是文件就进行路径和文件名拼接
                     */
                    $file_path = $path . DIRECTORY_SEPARATOR . $value;
                    /**
                     * 打开这个文件
                     */
                    $fp = fopen("$file_path", "r");
                    /**
                     * 获取详细信息
                     */
                    $info[$value] = stat($file_path);
                }
            }
            clearstatcache();
            /**
             * 传递参数去前台
             */
            $data['info'] = $info;
            $data['previous'] = $previous;
            $data['filesnames'] = $filesnames;
            $data['path'] = $path;
            $this->assign($data);
            /**
             * 加载视图
             */
            $this->display();
        } else {
            /**
             * 不是post请求访问根目录
             */
            $path = RUNTIME_PATH;
            /**
             * 查询根目录下的所有文件
             */
            $filesnames = scandir($path);
            /**
             * 对数组进行处理
             */
            unset($filesnames[0]);
            unset($filesnames[1]);
            foreach ($filesnames as $value) {
                /**
                 * 判断是否为文件
                 */
                if (strpos($value, '.')) {
                    /**
                     * 是文件就进行路径和文件名拼接
                     */
                    $file_path = $path . DIRECTORY_SEPARATOR . $value;
                    /**
                     * 打开这个文件
                     */
                    $fp = fopen("$file_path", "r");
                    /**
                     * 获取详细信息
                     */
                    $info[$value] = stat($file_path);
                }
            }
            /**
             * 传递参数去前台
             */
            $data['info'] = $info;
            $data['filesnames'] = $filesnames;
            $data['path'] = $path;
            $this->assign($data);
            /**
             * 加载视图
             */
            $this->display();
        }
    }

    /**
     * 功能：下载指定文件
     * 
     * @author 秦晓武
     */
    public function down()
    {
        /**
         * 判断是否是POST请求
         */
        if (IS_POST) {
            /**
             * 获取参数$path
             * $path :是要下载的文件名
             */
            $path = I('get.path');
            /**
             * 获取前端参数$path_one
             * $path_one:是前台传递过来的当前的路径
             */
            $path_one = I("path_one");
            /**
             * 路径和文件名拼接
             */
            $file_path = $path_one . DIRECTORY_SEPARATOR . $path;
            \Org\Net\Http::download($file_path);
            exit();
        }
    }

    /**
     * 功能：删除缓存文件
     *
     * @author 王淳熙
     */
    public function del()
    {
        $ids = I('get.ids');
        $ids = base64_decode($ids);
        if (! unlink($ids)) {
            $this->error('删除失败');
        } else {
            $this->success('删除成功');
        }
    }
}
