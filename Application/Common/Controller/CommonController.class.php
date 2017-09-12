<?php
namespace Common\Controller;

use Think\Controller;

/**
 * 全局父类
 */
class CommonController extends Controller
{

    /**
     * 前台控制器初始化
     */
    protected function _initialize()
    {
        /* 读取配置 */
        if (! F('config')) {
            $config = M('config')->getField('name,value');
            F('config', $config);
        } else {
            $config = F('config');
        }
        C($config); // 合并配置参数到全局配置
        $this->__autoload();
        if (IS_POST)
            $this->do_post();
        if (IS_GET)
            $this->do_get();
        $this->assign(I());
        $this->assign('cookie', cookie());
        $this->assign('session', session());
    }

    /**
     * 自动加载
     */
    protected function __autoload()
    {}

    /**
     * 分页功能
     * @time 2014-12-26
     * 
     * @param $model 表名            
     * @param $map 条件            
     * @param $order 排序            
     * @param $field 字段            
     * @param $limit 限制            
     * @param $group group            
     * @param $having having            
     * @author 郭文龙 <2824682114@qq.com>
     */
    function page($model, $map = array(), $order = '', $field = true, $limit = 10, $group = '', $having = '')
    {
        if (is_string($model)) {
            $model = M($model);
        }
        /* 默认查询未删除数据 */
        if (! isset($map['is_del'])) {
            $map['is_del'] = 0;
        }
        /* 除了后台，默认查询未禁用数据 */
        if ((MODULE_NAME != 'Backend') && ! isset($map['is_hid'])) {
            $map['is_hid'] = 0;
        }
        $page = intval(I('get.p'));
        /* 查询满足要求的总记录数 */
        if ($group) {
            $data['count'] = $count = $model->where($map)->count('distinct ' . $group);
        } else {
            $data['count'] = $count = $model->where($map)->count();
        }
        $data['pages'] = ceil($count / $limit);
        if (! IS_AJAX) {
            /* 如果当前页无数据，自动显示最后一页数据 */
            $page = min($page, $data['pages']);
        }
        $Page = new \Think\Page($count, $limit); /* 实例化分页类 传入总记录数和每页显示的记录数 */
        $Page->setConfig('header', '条数据'); /* 共有多少条数据 */
        $Page->setConfig('prev', "<"); /* 上一页 */
        $Page->setConfig('next', '>'); /* 下一页 */
        $Page->setConfig('first', '首页'); /* 第一页 */
        $Page->setConfig('last', '尾页'); /* 最后一页 */
        $data['page'] = $Page->show(); /* 分页显示输出 */
        /* 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取 */
        $data['list'] = $model->where($map)
            ->field($field)
            ->order($order)
            ->group($group)
            ->having($having)
            ->page("$page,$limit")
            ->select();
        if (! $data['list'])
            $data['list'] = array();
        $this->assign($data);
        return $data;
    }

    /**
     * 图片验证码
     */
    public function verify()
    {
        $config = array(
            'codeSet' => '0123456789',
            'imageW' => '320',
            'imageH' => '60',
            'fontSize' => '32',
            'length' => 4
        )
        // 'useCurve' =>false,
        // 'useNoise' =>false
        ;
        $verify = new \Think\Verify($config);
        $verify->entry(1);
    }

    /**
     * 检测输入的验证码是否正确，$code为用户输入的验证码字符串
     * 
     * @param string $code
     *            验证码
     * @param string $id
     *            当前ID
     */
    function check_verify($code, $id = '1')
    {
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }

    /**
     * 删除临时上传图片
     * 
     * @param int $id
     *            图片id
     */
    public function delTempFile($id)
    {
        if (! session("member_id")) {
            $this->error("没有登录不能进行此操作");
        }
        $info = get_info("file", array(
            "id" => $id
        ));
        $path = ltrim($info['save_path'], '/');
        if (file_exists($path)) {
            if (@unlink($path)) {
                $info = "删除成功";
            } else {
                $info = "删除失败";
            }
        } else {
            $info = "文件不存在，删除失败";
        }
        delete_data("file", array(
            "id" => $id
        ));
        $this->success($info);
    }

    /**
     * 文件上传
     */
    public function uploadFile()
    {
        $return = array(
            'status' => 1,
            'info' => '上传成功',
            'data' => ''
        );
        /* 调用文件上传组件上传文件 */
        $File = D('File');
        $file_driver = C('FILE_UPLOAD_DRIVER');
        $info = $File->upload($_FILES, C('FILE_UPLOAD'), C('FILE_UPLOAD_DRIVER'), C("UPLOAD_{$file_driver}_CONFIG"));
        /* 记录附件信息 */
        if ($info) {
            $return['status'] = 1;
            $return = array_merge($info['download'], $return);
        } else {
            $return['status'] = 0;
            $return['info'] = $File->getError();
        }
        
        /* 返回JSON数据 */
        $this->ajaxReturn($return);
    }

    /**
     * 文件上传
     */
    public function uploadFileAPK()
    {
        $return = array(
            'status' => 1,
            'info' => '上传成功',
            'data' => ''
        );
        /* 调用文件上传组件上传文件 */
        $File = D('File');
        $file_driver = C('FILE_UPLOAD_DRIVER');
        $info = $File->upload($_FILES, C('FILE_UPLOAD_APK'), C('FILE_UPLOAD_DRIVER'), C("UPLOAD_{$file_driver}_CONFIG"));
        /* 记录附件信息 */
        if ($info) {
            $return['status'] = 1;
            $return = array_merge($info['download'], $return);
        } else {
            $return['status'] = 0;
            $return['info'] = $File->getError();
        }
        
        /* 返回JSON数据 */
        $this->ajaxReturn($return);
    }

    /**
     * 上传图片
     * 
     * @author huajie <banhuajie@163.com>
     */
    public function uploadPicture()
    {
        
        /* 返回标准数据 */
        $return = array(
            'status' => 1,
            'info' => '上传成功',
            'data' => ''
        );
        
        /* 调用文件上传组件上传文件 */
        $Picture = D('File');
        $pic_driver = C('IMG_UPLOAD_DRIVER');
        $info = $Picture->upload($_FILES, C('IMG_UPLOAD'), C('IMG_UPLOAD_DRIVER'), C("UPLOAD_{$pic_driver}_CONFIG"));

        /* 记录图片信息 */
        if ($info) {
            $return['status'] = 1;
            $return = array_merge($info['download'], $return);
        } else {
            $return['status'] = 0;
            $return['info'] = $Picture->getError();
        }
        
        /* 返回JSON数据 */
        $this->ajaxReturn($return);
    }

    /**
     * 百度编辑器上传调用方法
     */
    public function ueditor()
    {
        $data = new \Ueditor\Ueditor();
        $this->ajaxReturn($data->output(), 'EVAL');
    }
    /**
     * 处理全局POST交互
     * @time 2015-08-15
     * 
     * @author 秦晓武
     */
    private function do_post()
    {
        $operate = I('operate') ? I('operate') : I('get.operate');
        switch ($operate) {
            /* 获取手机验证码 */
            case 'get_mobile_code':
                $code = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
                $member_info = session('member_info');
                $mobile = I('account') ? I('account') : $member_info['mobile'];
                $content = "您的校验码是：" . $code . "。请不要把校验码泄露给其他人。如非本人操作，可不用理会！";
                
                session('mobile_code', $code);
                session('mobile_key', $mobile);
                /* 测试时向前端返回验证码 */
                $con = $this->_send_mobild_code($mobile, $code);
                if($con=='0'){
                    $this->success('验证短信已发送，请注意查收');
                }else{
                    $this->success('短信发送失败，请联系系统管理员');
                }
                break;
            /* 获取邮箱验证码 */
            case 'get_email_code':
                $code = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
                $member_info = session('member_info');
                $email = I('account') ? I('account') : $member_info['email'];
                $subject = C('SITE_TITLE') . "验证码邮件";
                $body = "尊敬的" . C('SITE_TITLE') . "用户，您好：<br />您的验证码为:" . $code . "，如非本人操作，无需理会";
                
                /* 测试时向前端返回验证码 */
                $status = 1;
                if ($status == 1) {
                    session('email_code', $code);
                    session('email_key', $email);
                    $this->success('验证邮件已发送，请登录查看' . $code);
                } else {
                    $this->error('验证邮件发送失败，请联系客服');
                }
                break;
            
            case 'ajax_select2':
                $limit = 10;
                $data['info'] = L('QSRCXGJZ_ERROR');
                $data['status'] = 0;
                $keyword = I('post.q', null, '/\S+/');
                if (empty($keyword)) {
                    echo json_encode($data);
                    exit();
                }
                $page = I('post.page', 1, '/^[1-9]\d*$/');
                $start = ($page - 1) * $limit;
                $keyword = '%' . trim($keyword) . '%';
                $data = $this->select2_list($keyword, $start . ',' . $limit);
                echo json_encode($data);
                exit();
                break;
            default:
                ;
        }
    }

    /**
     * 处理全局GET交互
     * @time 2015-08-15
     * 
     * @author 秦晓武
     */
    private function do_get()
    {
        switch (I('operate')) {
            case 'get_code':
                $this->success(1);
                break;
            default:
                ;
        }
    }

    /**
     * Select 2 查询
     * 
     * @param string $keyword
     *            关键字
     * @param string $limit
     *            长度
     * @return array 结果数组
     */
    private function select2_list($keyword, $limit)
    {
        $array = array(
            'total_count' => 0,
            'items' => array()
        );
        $map = array();
        $count_field = 'id';
        $type = I('get.ajax_select2');
        switch ($type) {
            /* 区域经理列表 */
            case 'get_employee_id':
                $table = 'admin';
                
                $all_role = get_no_del('admin_role');
                $all_child = get_all_child_ids($all_role, 19, true);
                $map['role_id'] = array(
                    'in',
                    $all_child
                );
                $map['username'] = array(
                    'like',
                    $keyword
                );
                $field = 'id,username,nickname';
                $func = function ($var) {
                    $str = '%username%（%nickname%）';
                    return str_replace(array(
                        '%username%',
                        '%nickname%'
                    ), array(
                        $var['username'],
                        $var['nickname']
                    ), $str);
                };
                break;
            
            default:
                return $array;
                break;
        }
        $result = $this->page(M($table), $map, '', '', $limit);
        foreach ($result['list'] as $k => &$v) {
            $v = array(
                'id' => $v['id'],
                'text' => $func($v)
            );
        }
        $array['total_count'] = $result['count'];
        $array = $result['list'];
        return $array;
    }
}
