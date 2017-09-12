<?php
/**
 * API接口全局接口类
 */
namespace Common\Controller;

use Think\Controller;
use Think\Crypt;
use Common\Plugin\Sensitive;

/**
 * Common\Controller\ApiController
 * @author Administrator
 *
 */
class ApiController extends Controller
{

    /**
     * 前台控制器初始化
     */
    protected function _initialize()
    {
        /* 读取配置 */
        if (!F('config')) {
            $config = M('config')->getField('name,value');
            F('config', $config);
        } else {
            $config = F('config');
        }
        C($config); // 合并配置参数到全局配置
        if (method_exists($this, '__autoload'))
            $this->__autoload();
        $this->write_api_log();

    }

    /**
     * 请求API访问记录
     */
    public function write_api_log()
    {
        $data['group'] = U('');
        $data['url'] = U('', array(), true, true);
        $param = array(
            'get' => I('get.'),
            'post' => I('post.')
        );
        $data['param'] = json_encode($param);
        $data['method'] = I('server.REQUEST_METHOD');
        $data['add_time'] = date('Y-m-d H:i:s');
        try {
            $debug = M('api_count');
            $debug->add($data);
        } catch (\Exception $e) {
            write_debug(array($data, $e->getMessage()), '写日志失败');
        }
    }

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
    public function page($model, $map = [], $order = '', $field = true, $limit = 10, $group = '', $having = '')
    {
        if (is_string($model)) {
            $model = M($model);
        }
        /* 默认查询未删除数据 */
        if (!isset($map['is_del'])) {
            $map['is_del'] = 0;
        }
        /* 除了后台，默认查询未禁用数据 */
        if ((MODULE_NAME != 'Backend') && !isset($map['is_hid'])) {
            $map['is_hid'] = 0;
        }
        $page = intval(I('get.p'));
        $data['count'] = $count = $model->where($map)
            ->group($group)
            ->having($having)
            ->count(); // 查询满足要求的总记录数
        $data['pages'] = ceil($count / $limit);
        /* 如果当前页无数据，自动显示最后一页数据 */
        // if(!IS_AJAX){
        // $page = min($page,$data['pages']);
        // }

        $Page = new \Think\Page($count, $limit); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('header', '条数据'); // 共有多少条数据
        $Page->setConfig('prev', "<"); // 上一页
        $Page->setConfig('next', '>'); // 下一页
        $Page->setConfig('first', '首页'); // 第一页
        $Page->setConfig('last', '尾页'); // 最后一页
        $data['page'] = $Page->show(); // 分页显示输出
        /* 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取 */
        $data['list'] = $model->where($map)
            ->field($field)
            ->order($order)
            ->group($group)
            ->having($having)
            ->page("$page,$limit")
            ->select();
        $this->assign($data);
        return $data;
    }

    /**
     * 接口封装输出
     * @param array $data 内容数组
     * @param string $type 输出类型
     */
    public function apiReturn($data, $type = 'json')
    {
        $defult = array(
            'status' => '0',
            'msg' => 'error !',
        );
        if (!isset($data)) {
            $data = $defult;
        } else {
            $data = array_merge($defult, $data);
        }
        $this->filter_field($data);
        array_walk_recursive($data, function (&$a) use (&$data) {
            if ($a === null)
                $a = '';
            if (is_numeric($a))
                $a = strval($a);

        });
        $this->ajaxReturn($data, $type);
    }

    /**
     * 过滤掉无用数据字段
     * @param $data
     * @return mixed
     */
    protected function filter_field(&$data)
    {
        foreach ($data as $key => &$val) {
            if ($val == null || empty($val)) {
                unset($data[$key]);
            } elseif (is_array($val)) {
                    $this->filter_field($val);
            }else{
                //echo '['.$key.' '.$val.']';
            }
        }
        return $data;
    }

    /**
     * 过滤敏感词
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    public function remove_sensitive($str)
    {
        if (!$str) return $str;
        $sensitive = new Sensitive();
        $bad_words = get_no_del('sensitive');
        $bad_words = array_column($bad_words, 'name');
        return $sensitive->filterSensitive($str, $bad_words);
    }

    /**
     * 生成批量插入sql
     *
     * @param
     *            data 二维数组
     * @param
     *            table 表名
     * @author 陆龙飞
     * @date 2016-01-19
     * @return string
     *
     */
    public function addSql($data, $table)
    {
        if (empty($data) || empty($table))
            return '';
        $value = implode('`,`', array_keys($data[0]));
        $sql = 'INSERT INTO `' . $table . '` (`' . $value . '`) VALUES ';
        foreach ($data as $val) {
            $sql .= "(";
            foreach ($val as $k => $v) {
                is_numeric($v) ? $sql .= $v : $sql .= "'" . $v . "'";
                $sql .= ',';
            }
            $sql = rtrim($sql, ',');
            $sql .= '),';
        }
        $sql = rtrim($sql, ',');

        return $sql;
    }

    /**
     * 只展示的想要的字段（只适用一维数组）
     * @param   $[data] [数组]
     * @param   $[field] [保留字段的数组]
     * @return [type] [description]
     */
    public function pick_char($data, $field)
    {
        if ($field) {
            $_field = explode(',', $field);
        }
        foreach ($data as $key => $value) {
            if (in_array($key, $_field)) {
                $_data[$key] = $value;
            }
        }
        return $_data;
    }
}