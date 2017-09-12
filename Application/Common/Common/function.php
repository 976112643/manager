<?php
/**核心函数库*/
define('EMAIL', '/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z]{2,3})+$/');
define('MOBILE', '/^1[3|4|5|7|8][0-9]{9}$/');
/**
 * 两位小数
 */
define('DECIMAL', '/^[1-9]\d*(\.\d{1,2})?$|^0(\.\d{1,2})?$/');
/**
 * 8-30位密码（必须包含数字字母和特殊字符）
 */
define('NUM_CHAR_SPECIAL', '/^(?=.*[0-9])(?=.*[a-zA-Z])(?=.*[^a-zA-Z0-9]).{8,30}$/');
/**
 * 传真
 */
define('FIXED', '/^(?!.{0})(0[0-9]{2,3}\-)?([2-9][0-9]{6,7})+(\-[0-9]{1,4})?$/');
/**
 * 邮编
 */
define('ZIPCODE', '/^[1-9]\d{5}$/');

/**
 * 网址
 */
define('URL', '/^^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/');

/**
 * 数据增删改查操作
 * 
 * @package
 *
 */
/**
 * 事务封装
 * 
 * @param function $action
 *            打包函数，所有封装SQL返回结果合并到RESULT数组内
 * @return string 失败时返回错误信息
 *         @time 2015-06-17
 * @author 秦晓武
 */
function trans($action = '')
{
    if (! is_callable($action))
        return false;
    M()->startTrans();
    $result = $action();
    /* 检测所有结果必须是数字 */
    if (array_reduce($result, function (&$res, $a) {
        return $res && is_numeric($a);
    }, true)) {
        M()->commit();
        return '';
    } else {
        M()->rollback();
        foreach ($result as $key => $value) {
            if (is_numeric($value)) {
                unset($result[$key]);
                continue;
            } else {
                $result[$key] = $value;
            }
        }
        ;
        return implode('|', $result);
    }
}

/**
 * 获取所有未删除数据
 * 
 * @param string $table
 *            表名
 * @param string $sort
 *            排序字段
 * @return string $result 结果数组
 *         @time 2015-05-22
 * @author 秦晓武
 */
function get_no_del($table = '', $sort = 'sort desc')
{
    if (! $table)
        return array();
    /**
     * 以表名_all命名缓存
     */
    $file = $table . '_no_del';
    if (! F($file)) {
        $all = get_result($table, array(
            'is_del' => 0
        ), $sort);
        F($file, array_format($all));
    }
    return F($file);
}

/**
 * 获取所有未禁用数据
 * 
 * @param string $table
 *            表名
 * @param string $sort
 *            排序字段
 * @return string $result 结果数组
 *         @time 2015-05-22
 * @author 秦晓武
 */
function get_no_hid($table = '', $sort = 'sort desc')
{
    if (! $table)
        return array();
    /**
     * 以表名_all命名缓存
     */
    $file = $table . '_no_hid';
    if (! F($file)) {
        $all = get_result($table, array(
            'is_del' => 0,
            'is_hid' => 0
        ), $sort);
        F($file, array_format($all));
    }
    return F($file);
}

/**
 * 获取数据集
 * 
 * @param $model 表模型            
 * @param $map 查询条件            
 * @param $field 查询字段            
 * @param $order 排序            
 * @param $limit 条数            
 * @param $group 分组            
 * @param $having 附带条件            
 *
 */
function get_result($model, $map = array(), $order = '', $field = true, $limit = '', $group = '', $having = '')
{
    /* 转换为模型 */
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
    $result = $model->where($map)
        ->field($field)
        ->order($order)
        ->group($group)
        ->having($having)
        ->limit($limit)
        ->select();
    return $result;
}

/**
 * 获取单条数据
 * 
 * @param $model 表模型            
 * @param $map 查询条件            
 * @param $field 查询字段            
 * @param $order 排序            
 *
 */
function get_info($model, $map = array(), $field = true, $order = '')
{
    $result = array();
    if (is_string($model)) {
        $model = M($model);
    }
    /* 没有$map时, 返回所有字段为空的数据，保持返回类型的统一 */
    if (! $map || (isset($map['id']) && ! $map['id'])) {
        foreach ($model->getDbFields() as $key) {
            $result[$key] = '';
        }
        return $result;
    }
    /* 默认查询未删除数据 */
    if (! isset($map['is_del'])) {
        $map['is_del'] = 0;
    }
    /* 除了后台，默认查询未禁用数据 */
    if ((MODULE_NAME != 'Backend') && ! isset($map['is_hid'])) {
        $map['is_hid'] = 0;
    }
    $result = $model->where($map)
        ->field($field)
        ->order($order)
        ->find();
    return $result;
}

/**
 * 添加、更新数据
 * 
 * @param $Model 表模型            
 * @param $rules 查询规则            
 * @param $map 查询条件            
 * @param $data 数据            
 *
 */
function update_data($model, $rules = array(), $map = array(), $data = array())
{
    /* 转换为模型 */
    if (is_string($model)) {
        $model = M($model);
    }
    /* 默认读取POST数据 */
    if (empty($data)) {
        $data = $_POST;
    }
    $data = $model->validate($rules)->create($data);
    /* 数据对象创建错误 */
    if (! $data) {
        return $model->getError();
    }
    /* 初始化时间 */
    $data['updatetime'] = time();
    $data['update_time'] = date('Y-m-d H:i:s');
    
    /* 记录操作员ID，前台为member_id，后台为admin_id */
    if ((MODULE_NAME != 'Backend') && ! isset($map['is_hid']) && !isset($data['member_id'])) {
        //$data['member_id'] = session('member_id');
    } else {
        $data['admin_id'] = session('member_id');
    }
    if (empty($map) && empty($data[$model->getPk()])) {
        /* 添加 */
        $data['addtime'] = time();
        $data['add_time'] = date('Y-m-d H:i:s');
        $result = $model->add($data);
    } else {
        /* 修改 */
        if (empty($map)) {
            $result = $model->save($data);
        } else {
            $result = $model->where($map)->save($data);
        }
    }
    if (! is_numeric($result)) {
        return $result;
    }
    $id = $result;
    if (! empty($data[$model->getPk()])) {
        $result = $id = $data[$model->getPk()];
    }
    if (! empty($map[$model->getPk()])) {
        if (is_array($map[$model->getPk()])) {
            $id = implode(',', $map[$model->getPk()][1]);
        } else {
            $result = $id = $map[$model->getPk()];
        }
    }
    /* 执行更新后的回调函数 */
    $trace = debug_backtrace();
    if (isset($trace[1]['object']) && is_object($trace[1]['object']) && method_exists($trace[1]['object'], 'call_back_change')) {
        $trace[1]['object']->call_back_change($id);
    }
    return $result;
}

/**
 * 添加数据
 * 
 * @param $model 表模型            
 * @param $map 条件            
 * @param $rules 验证            
 *
 */
function add_data($model, $rules = array(), $map = array())
{
    if (is_string($model)) {
        $model = M($model);
    }
    // 创建数据对象
    $data = $model->validate($rules)->create();
    if (! $data) { // 数据对象创建错误
        return $model->getError();
    }
    $res = $model->add($data);
    /* 执行更新后的回调函数 */
    $trace = debug_backtrace();
    if (isset($trace[1]['object']) && is_object($trace[1]['object']) && method_exists([
        $trace[1]['object'],
        'call_back_change'
    ], true)) {
        $trace[1]['object']->call_back_change($res);
    }
    return $res;
}

/**
 * 删除数据
 * 
 * @param $Model 表模型            
 * @param $map 条件            
 *
 */
function delete_data($Model, $map = array())
{
    if (is_string($Model)) {
        $Model = M($Model);
    }
    $Model->where($map)->delete();
    /* 执行更新后的回调函数 */
    $trace = debug_backtrace();
    if (isset($trace[1]['object']) && is_object($trace[1]['object']) && method_exists([
        $trace[1]['object'],
        'call_back_change'
    ], true)) {
        /* 获取数据ID */
        if (isset($map['id'])) {
            $id = is_array($map['id']) ? implode(',', $map['id'][1]) : $map['id'];
        }
        $trace[1]['object']->call_back_change($id);
    }
    return true;
}

/**
 * 统计数据
 * 
 * @param $Model 表模型            
 * @param $map 条件            
 * @param $field 字段            
 *
 */
function count_data($Model, $map = array(), $field = 'id')
{
    if (is_string($Model)) {
        $Model = M($Model);
    }
    $count = $Model->where($map)->count($field);
    return $count;
}

/**
 * 查询数据的sql操作
 * 
 * @param $sql sql语句            
 *
 */
function query_sql($sql)
{ // 查询数据的sql操作
    $result = M()->query($sql);
    session('sql', M()->getLastSql());
    return $result;
}

/**
 * 更新和写入数据的sql操作
 * 
 * @param $sql sql语句            
 *
 */
function execute_sql($sql)
{ // 更新和写入数据的sql操作
    $result = M()->execute($sql);
    session('sql', M()->getLastSql());
    return $result;
}

// =========== 数据增删改查操作 end ==========//

/**
 * 产生导航树
 * 
 * @param $list 结果集            
 * @param $pk ID号            
 * @param $pid 父id            
 * @param $child 子树字段            
 * @param
 *            $root
 * @param
 *            $key
 *            
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0, $key = '')
{
    // 创建Tree
    $tree = array();
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $k => $data) {
            $refer[$data[$pk]] = & $list[$k];
        }
        foreach ($list as $k => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                if ($key != '') {
                    $tree[$data[$key]] = & $list[$k];
                } else {
                    $tree[] = & $list[$k];
                }
            } else {
                if (isset($refer[$parentId])) {
                    $parent = & $refer[$parentId];
                    if ($key != '') {
                        $parent[$child][$data[$key]] = & $list[$k];
                    } else {
                        $parent[$child][] = & $list[$k];
                    }
                }
            }
        }
    }
    return $tree;
}

/**
 * 把 int类型转换为 string类型
 * 
 * @param $data 数据            
 * @param $map 条件            
 *
 */
function int_to_string($data, $map = array('status'=>array(1=>'正常',-1=>'删除',0=>'禁用')))
{
    if ($data === false || $data === null) {
        return $data;
    }
    $data = (array) $data;
    foreach ($data as $key => $row) {
        foreach ($map as $col => $pair) {
            if (isset($row[$col]) && isset($pair[$row[$col]])) {
                $data[$key][$col . '_text'] = $pair[$row[$col]];
            }
        }
    }
    return $data;
}

/**
 * 根据字段的值进行数组过滤
 * 
 * @param $arr 要过滤的数组            
 * @param $key 要过滤的值            
 *
 */
function assoc_unique($arr, $key)
{
    $tmp_arr = array();
    foreach ($arr as $k => $v) {
        if (in_array($v[$key], $tmp_arr)) { // 搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
            unset($arr[$k]);
        } else {
            $tmp_arr[] = $v[$key];
        }
    }
    return $arr;
}

/**
 * 多图上传
 * 
 * @param $picture_ids 临时图片ID            
 * @param $folder 上传目录            
 * @param $image_field 数据            
 * @param $table 图片保存数据表            
 * @param $image_field 数据            
 * @param $table_key_field 图片保存数据表对应字段名ID            
 * @param $table_key_value 图片保存数据表的ID值            
 *
 */
function multi_file_upload($picture_ids, $folder, $table, $table_key_field, $table_key_value, $image_field = 'image')
{
    $posts = $_POST;
    $return = false;
    if ($picture_ids != '') {
        // 创建目录
        $new_folder = mk_dir($folder);
        if ($new_folder == true) {
            $new_folder = $folder;
        } else 
            if ($new_folder == false) {
                return '目录创建失败';
            }
        if (is_array($picture_ids)) {
            $picture_ids = implode(',', $picture_ids);
        }
        $picture_ids = addslashes($picture_ids);
        
        if ($picture_ids == '') {
            $picture_ids = '0';
        }
        $result = get_result("file", array(
            'id' => array(
                'in',
                $picture_ids
            )
        ), '', "file_name,save_path");
        $msg = '';
        foreach ($result as $row) {
            $path = ltrim($row['save_path'], '/');
            $file_name = basename($path);
            $new_path = $new_folder . '/' . $file_name;
            copy($path, $new_path);
            @unlink($path);
            $_POST = null;
            $_POST['file_name'] = $row['file_name'];
            $_POST[$image_field] = $new_path;
            $_POST[$table_key_field] = $table_key_value;
            $return = update_data($table);
        }
        delete_data("file", array(
            'id' => array(
                'in',
                $picture_ids
            )
        ));
    }
    $_POST = $posts;
    
    return $return;
}

/**
 * 创建目录
 * 
 * @param $dir 目录            
 * @param $mode 权限            
 *
 */
function mk_dir($dir, $mode = 0777)
{
    if (is_dir($dir) || @mkdir($dir, $mode, true)) {
        return true;
    }
    if (! mk_dir(dirname($dir), $mode)) {
        return false;
    }
}

/**
 * 生成缩略图
 * 
 * @param $image 传入的图片路径            
 * @param $width 宽度            
 * @param $height 高度            
 * @param $prefix 前缀
 *            l-large / m-middle / s-small
 * @param $thumb_path 缩略图保存位置            
 * @param $default_pic 没有图片时，默认返回图片            
 *
 */
function thumb($image, $width = 200, $height = 200, $prefix = 'S', $default_pic = '')
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
                $image = new Image($image);
                $image->resize($width, $height);
                $image->save($new_image);
            } else {
                copy($image, $new_image);
            }
        }
        return __ROOT__ . '/' . $new_image;
    } else {
        $name = basename($image);
        $new_image_default = dirname($image) . '/' . $prefix . $name;
        if (! file_exists($new_image_default) && file_exists($default_pic)) {
            list ($width_orig, $height_orig) = getimagesize($default_pic);
            if ($width_orig != $width || $height_orig != $height) {
                $image = new Image($default_pic);
                $image->resize($width, $height);
                $image->save($new_image_default);
            } else {
                copy($default_pic, $new_image_default);
            }
        }
        return __ROOT__ . '/' . $new_image_default;
    }
}

/**
 * 删除缩略图
 * 
 * @param $image 传入的图片路径            
 *
 */
function del_thumb($image)
{
    if (file_exists($image)) {
        @unlink($image);
    } else {
        return false;
    }
    $name = basename($image);
    $thumb_l = dirname($image) . '/L' . $name;
    if (file_exists($thumb_l)) {
        @unlink($thumb_l);
    }
    $thumb_m = dirname($image) . '/M' . $name;
    if (file_exists($thumb_m)) {
        @unlink($thumb_m);
    }
    $thumb_s = dirname($image) . '/S' . $name;
    if (file_exists($thumb_s)) {
        @unlink($thumb_s);
    }
    return true;
}

/**
 * 返回路径中的参数
 * 
 * @author 康利民 <3027788306@qq.com> 2015-01-30
 * @param $add 需要添加的参数            
 * @param $del 需要过滤掉的参数，使用逗号分隔            
 * @return 参数数组
 * @example 说明：给url添加排序条件sort，同时删除掉url中的category和author参数及它们的值
 *          I("index",param(array('sort'=>'id'),'category,author'))
 *         
 */
function param($add = array(), $del = '')
{
    $arr = array_merge(I('get.'), $add);
    $delArr = explode(',', $del);
    foreach ($delArr as $val) {
        unset($arr[$val]);
    }
    return $arr;
}

/**
 * 返回真实路径
 * 
 * @param $url 地址            
 *
 */
function ass_url($url)
{
    $menu['url'] = $url;
    $chars = "/((^http)|(^https)|(^ftp)):\/\/(\S)+\.(\w)+/";
    if (! preg_match($chars, $menu['url'])) {
        $return_url = __ROOT__ . '/' . $url;
    } else {
        $return_url = $url;
    }
    return $return_url;
}

/**
 * 系统加密方法
 * 
 * @param $data 要加密的字符串            
 * @param $key 加密密钥            
 * @param $expire 过期时间
 *            单位 秒
 * @return string
 *
 */
function think_encrypt($data, $key = '', $expire = 0)
{
    $key = md5(empty($key) ? C('SECURITY_AUTHKEY') : $key);
    $data = base64_encode($data);
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    $char = '';
    
    for ($i = 0; $i < $len; $i ++) {
        if ($x == $l)
            $x = 0;
        $char .= substr($key, $x, 1);
        $x ++;
    }
    
    $str = sprintf('%010d', $expire ? $expire + time() : 0);
    
    for ($i = 0; $i < $len; $i ++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
    }
    return str_replace(array(
        '+',
        '/',
        '='
    ), array(
        '-',
        '_',
        ''
    ), base64_encode($str));
}

/**
 * 系统解密方法
 * 
 * @param $data 要解密的字符串
 *            （必须是think_encrypt方法加密的字符串）
 * @param $key 加密密钥            
 *
 */
function think_decrypt($data, $key = '')
{
    $key = md5(empty($key) ? C('SECURITY_AUTHKEY') : $key);
    $data = str_replace(array(
        '-',
        '_'
    ), array(
        '+',
        '/'
    ), $data);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    $data = base64_decode($data);
    $expire = substr($data, 0, 10);
    $data = substr($data, 10);
    
    if ($expire > 0 && $expire < time()) {
        return '';
    }
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    $char = $str = '';
    
    for ($i = 0; $i < $len; $i ++) {
        if ($x == $l)
            $x = 0;
        $char .= substr($key, $x, 1);
        $x ++;
    }
    
    for ($i = 0; $i < $len; $i ++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

/**
 * 获取传递过来的字符中的所有本地图片
 * 
 * @param $content 内容            
 * @param $order 排序            
 *
 */
function get_imgs($content, $order = '')
{
    $pattern = "/<img.*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
    preg_match_all($pattern, $content, $match);
    if (isset($match[1]) && ! empty($match[1])) {
        if ($order != '' && is_numeric($order) && isset($match[1][$order])) {
            return $match[1][$order];
        } else {
            return $match[1];
        }
    }
    return '';
}

/**
 * 删除传递过来的字符中的所有本地图片
 * 
 * @param $content 内容            
 *
 */
function del_str_imgs($content)
{
    // 获取字符中的所有图片
    $img_list = get_imgs($content);
    foreach ($img_list as $key => $val) {
        if (file_exists($val)) {
            @unlink($val);
        }
    }
    return '';
}

/**
 * 替换/还原本地图片路径
 * 
 * @param $str 字符串            
 * @param $type 类型            
 *
 */
function replace_str_img($str, $type = "")
{
    if ($type == "replace") {
        $str = str_replace('http://' . $_SERVER['SERVER_NAME'], '', $str);
        $str = str_replace('https://' . $_SERVER['SERVER_NAME'], '', $str);
        $str = str_replace(__ROOT__ . '/Uploads', 'Uploads', $str);
    } else {
        $str = str_replace('Uploads', __ROOT__ . '/Uploads', $str);
    }
    return $str;
}

/**
 * 文件上传（单个|多个）
 * 
 * @param $md5 临时文件md5            
 * @param $folder 上传目录            
 * @param $table 图片保存数据表            
 * @param $field 图片保存数据表对应字段名ID            
 * @param $value 图片保存数据表的ID值            
 * @param $file_field 文件路径保存字段            
 *
 */
function file_upload_multi($md5, $folder, $table, $field, $value, $file_field = 'image')
{
    $return = '';
    if ($md5 != '') {
        $new_folder = mk_dir($folder);
        if ($new_folder == true) {
            $new_folder = $folder;
        } elseif ($new_folder == false) {
            return '目录创建失败';
        }
        if (is_array($md5)) {
            $md5 = implode(',', $md5);
        }
        $md5 = addslashes($md5);
        if ($md5 == '') {
            $md5 = '0';
        }
        $map = array(
            'md5' => array(
                'IN',
                $md5
            )
        );
        $result = get_result('file', $map, '', 'file_name,save_path');
        $msg = '';
        foreach ($result as $row) {
            $path = ltrim($row['save_path'], '/');
            $file_name = basename($path);
            $new_path = $new_folder . '/' . $file_name;
            copy($path, $new_path);
            @unlink($path);
            $data = array(
                'file_name' => $row['file_name'],
                $file_field => $new_path
            );
            $return = update_data($table, array(), array(
                $field => $value
            ), $data);
        }
        delete_data('file', $map);
    }
    return $return;
}

/**
 * 根据模块地址获取SEO信息
 * 
 * @param $model_url 模块路径
 *            return array(
 *            title //网页标题
 *            keywords //网页关键词
 *            description //网页描述
 *            )
 *            @time 2016-03-30
 * @author 陆龙飞
 *        
 */
function get_page_seo_info($model_url = '')
{
    if (empty($model_url))
        $model_url = MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME;
    
    $seo = array();
    $map = array(
        'is_hid' => 0,
        'is_del' => 0
    );
    $field = array(
        'id',
        'page_title as title',
        'keywords',
        'description',
        'url'
    );
    if (F('seo_page')) {
        $result = F('seo_page');
    } else {
        $result = get_result('seo_page', $map, '', $field);
        F('seo_page', $result);
    }
    foreach ($result as $val) {
        if (strtolower($model_url) === strtolower($val['url'])) {
            $seo['title'] = $val['title'];
            $seo['keywords'] = $val['keywords'];
            $seo['description'] = $val['description'];
            break;
        }
    }
    ! empty($seo['title']) ? $seo['title'] .= ' | ' . C('SITE_TITLE') : $seo['title'] = C('SITE_TITLE');
    if (empty($seo['keywords']))
        $seo['keywords'] = C('SITE_KEYWORD');
    if (empty($seo['description']))
        $seo['description'] = C('SITE_DESCRIPTION');
    
    return $seo;
}

/**
 * 获取店铺推荐商品信息
 * 1、获取商品里推荐的商品
 * 2、把推荐商品存入商铺推荐字段里
 * 3、如果传入的id不正确，更新所有的商铺
 * 
 * @param $shop_id 店铺id            
 *
 */
function get_shop_recommend($shop_id)
{
    $Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
    /* 获取当前时间 */
    $time = time();
    /* 判断当前的传入的ID是否正确 */
    if (is_numeric($shop_id) && $shop_id != 0) {
        /* 正确执行SQL获取推荐商品 */
        $arr_id = $Model->query("SELECT id FROM sr_product WHERE shop_id = $shop_id AND is_hid = 0 AND is_del = 0 AND is_sale =1 AND end_time>$time ORDER BY recommend DESC,sort DESC ,id DESC LIMIT 0,2");
        $_POST = null;
        /* 获取要最新的推荐商品 */
        $arr_id = array_column($arr_id, 'id');
        $_POST['home_recommend_ids'] = implode(',', $arr_id);
        $_POST['id'] = $shop_id;
        /* 修改数据库 */
        update_data('shop');
    } else {
        /* 传入ID有误就查询所有店铺 */
        $map['is_del'] = 0;
        $map['is_hid'] = 0;
        $map['status'] = 1;
        $ids = get_result('shop', $map, '', 'id');
        if (empty($ids)) {
            return 1;
        }
        /* 获取店铺ID */
        $ids_shop = array_column($ids, 'id', 'id');
        $ids = implode(',', array_column($ids, 'id'));
        $map = null;
        /* 查询所有店铺下的所有商品 */
        $map['shop_id'] = array(
            'in',
            $ids
        );
        $map['end_time'] = array(
            'GT',
            $time
        );
        $map['is_del'] = 0;
        $map['is_hid'] = 0;
        $map['is_sale'] = 1;
        $result = get_result('product', $map, 'recommend DESC,sort DESC ,id DESC');
        /* 处理商品的结果集，把商铺ID作为键值整理商品 */
        foreach ($result as $value) {
            if (! empty($ids_shop[$value['shop_id']])) {
                $shop[$value['shop_id']][] = $value;
            }
        }
        // $shop = array_merge($shop,$ids_shop);
        /* 循环所有商品ID */
        foreach ($ids_shop as $key => $value) {
            $_POST = null;
            $_POST['id'] = $key;
            /* 判断商品的键值下是否存在商品如果是就取出推荐商品 */
            if ($shop[$key] && is_array($shop[$key])) {
                /* 统计数组个数 */
                $sum = count($shop[$key]);
                $id_product = null;
                /* 取数组的前两个元素作为推荐商品 */
                foreach ($shop[$key] as $ke => $val) {
                    if (($ke + 1 == $sum && $ke < 2) || $ke == 1) {
                        $id_product .= $val['id'];
                    } elseif ($ke + 1 < $sum || $key == 0) {
                        $id_product .= $val['id'] . ',';
                    }
                }
                $_POST['home_recommend_ids'] = $id_product;
            } else {
                /* 如果不是推荐商品就为0 */
                $_POST['home_recommend_ids'] = 0;
            }
            /* 修改数据库 */
            update_data('shop');
        }
    }
}

/**
 * 获取用户头像
 * 
 * @param int $member_id
 *            用户ID
 * @param int $size
 *            头像尺寸
 * @return string 头像路径
 *        
 */
function get_avatar($member_id = '', $size = "")
{
    if ($member_id == "") {
        $member_id = session("member_id");
    }
    if ($size) {
        $member_id = $member_id . '_' . $size;
    }
    $file_j = "Uploads/Avatar/" . $member_id . ".jpg";
    $file_p = "Uploads/Avatar/" . $member_id . ".png";
    $file_g = "Uploads/Avatar/" . $member_id . ".gif";
    if (file_exists($file_j)) {
        return __ROOT__ . '/' . $file_j . '?' . time();
    } elseif (file_exists($file_p)) {
        return __ROOT__ . '/' . $file_p . '?' . time();
    } elseif (file_exists($file_g)) {
        return __ROOT__ . '/' . $file_g . '?' . time();
    } else {
        $file_path = __ROOT__ . "/Public/Static/img/";
        if ($size) {
            return $file_path . "avatar_" . $size . ".jpg";
        } else {
            return $file_path . "avatar.jpg";
        }
    }
}

/**
 * 数组键名替换，无限级
 * @param array | string $old 待替换数组
 * @param array $new 替换后数组
 * @param array $change 新老键名对数组array('old_key_1'=>'new_key_1',...)
 * @return 引用传递，直接调用第二个参数$new即可
 */
function array_change_key(&$old, &$new, &$change)
{
    if (! is_array($old)) {
        return $new = $old;
    }
    foreach ($old as $k => $v) {
        $k_1 = isset($change[$k]) ? $change[$k] : $k;
        array_change_key($v, $new[$k_1], $change);
    }
}

/**
 * 结果集数组格式化
 * 
 * @param array $result
 *            数据库查询结果集
 * @param string $by_filed
 *            格式化后数组的key,默认为ID
 * @return array 返回格式化后数组
 *         @time 2015-06-06
 * @author 秦晓武
 */
function array_format($result = array(), $by_filed = 'id')
{
    $format_array = array();
    foreach ($result as $row) {
        $format_array[$row[$by_filed]] = $row;
    }
    return $format_array;
}

/**
 * 两个多维数组合并（后面值覆盖前面值）
 * 
 * @param array $a1
 *            多维数组
 * @param array $a2
 *            多维数组, 用"delete"标记删除的键值
 * @return array 返回合并后数组
 *         @time 2015-06-06
 * @author 秦晓武
 */
function array_overlay($a1 = array(), $a2 = array())
{
    $diff = array_diff_key($a2, $a1);
    foreach ($diff as $k => $v) {
        $a1[$k] = $v;
    }
    foreach ($a1 as $k => $v) {
        if (isset($a2[$k]) && $a2[$k] === "delete") {
            unset($a1[$k]);
            continue;
        }
        ;
        if (! array_key_exists($k, $a2))
            continue;
        if (is_array($v) && is_array($a2[$k])) {
            $a1[$k] = array_overlay($v, $a2[$k]);
        } else {
            $a1[$k] = $a2[$k];
        }
    }
    return $a1;
}

/**
 * XML转换成数组
 * @time 2016-07-28
 * 
 * @param array $xml
 *            要格式化的数据
 * @return array $result 格式化后数组
 * @author 秦晓武
 */
function xml_to_array($xml)
{
    $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
    if (preg_match_all($reg, $xml, $matches)) {
        $count = count($matches[0]);
        for ($i = 0; $i < $count; $i ++) {
            $subxml = $matches[2][$i];
            $key = $matches[1][$i];
            if (preg_match($reg, $subxml)) {
                $arr[$key] = xml_to_array($subxml);
            } else {
                $arr[$key] = $subxml;
            }
        }
    }
    return $arr;
}

/**
 * 数组转换成PHP文件格式
 * @time 2015-05-19
 * 
 * @param array $data
 *            要格式化的数据
 * @return array $result 格式化后数组
 * @author 秦晓武
 */
function array_to_php($data = array())
{
    if (! is_array($data)) {
        $data = array();
    }
    return "<?php \r\n return " . var_export($data, true) . ";";
}

/**
 * 数组转树形(无限级)
 * 
 * @param array $data
 *            输入数组，二维结构
 * @param array $set
 *            配置数组
 * @example "[
 *          'field' => [ //字段设置
 *          'self' => 当前记录KEY，默认'id'
 *          'parent' => 父级标记KEY，默认'pid'
 *          ],
 *          'root' => 起始节点，默认'0'(最上层父级ID)
 *          ]"
 * @return array 结果数组
 *         @time 2015-05-22
 * @author 秦晓武
 */
function array_to_tree(&$data, $set = array())
{
    if (! is_array($data) || ! is_array($set) || ! count($data)) {
        return array();
    }
    /* 初始化设置 */
    $default_set = array(
        'field' => array(
            'self' => 'id',
            'parent' => 'pid'
        ),
        'function' => array(
            'format_data' => ''
        ), 
        'root' => '0',
        'root_show' => false,
        'level' => 0
    );
    $set = array_overlay($default_set, $set);
    $field_self = $set['field']['self'];
    $field_parent = $set['field']['parent'];
    $f = $set['function']['format_data'];
    $tree = array();
    foreach ($data as $key => $row) {
        $temp_row = '';
        if (! isset($row[$field_parent]) || $row[$field_parent] == $set['root'] || ($row[$field_self] == $set['root'] && $set['root_show'])) {
            if (is_callable($f)) {
                $temp_row = call_user_func_array($f, array(
                    $row,
                    $set
                ));
            }
            $tree[$row[$field_self]]['data'] = $temp_row ? $temp_row : $row;
            unset($data[$key]);
        }
        if ($set['root_show'] && ($row[$field_self] == $set['root'])) {
            break;
        }
    }
    foreach ($tree as $key => $value) {
        $temp_set = $set;
        $temp_set['root'] = $key;
        $temp_set['level'] ++;
        $tree[$key]['child'] = array_to_tree($data, $temp_set);
    }
    return $tree;
}

/**
 * 树转数组
 * 
 * @param array $tree
 *            树型数组（data为父级数据，child为子级数据）
 * @return array 返回转换后数组
 *         @time 2015-06-06
 * @author 秦晓武
 */
function tree_to_array($tree = array())
{
    $data = array();
    foreach ($tree as $row) {
        $data[$row['data']['id']] = $row['data'];
        if ($row['child']) {
            $data = $data + tree_to_array($row['child']);
        }
    }
    return $data;
}

/**
 * 树内搜索
 * 
 * @param array $tree
 *            树型数组（data为父级数据，child为子级数据）
 * @param string $key
 *            数据
 * @return array 返回整个节点数据
 *         @time 2015-06-06
 * @author 秦晓武
 */
function search_in_tree($tree, $key = '')
{
    if (! is_array($tree))
        return '';
    if ($tree[$key])
        return $tree[$key];
    foreach ($tree as $value) {
        $row = search_in_tree($value, $key);
        if ($row)
            return $row;
    }
}

/**
 * 树形显示
 * 
 * @param array $tree
 *            输入数组
 * @param array $set
 *            array( //配置数组
 *            'show' => 显示字段
 *            'field' => array( //字段设置
 *            'self' => 当前记录KEY，默认'id'
 *            'parent' => 父级标记KEY，默认'pid'
 *            'relation' => 外键KEY，用于下拉框级联操作，默认''
 *            ),
 *            'function' => array( //回调函数
 *            'replace_self' => 替换子级的回调函数
 *            'replace_parent' => 替换父级的回调函数
 *            ),
 *            'node' => 起始节点，默认'0'（最上层父级ID）
 *            'level' => 层级，默认'-1'
 *            'limit' => 最大层级，默认'999'
 *            'prefix' => 显示前缀，默认'|- '
 *            'add_prefix' => 递增前缀，默认'&nbsp;&nbsp;&nbsp;'
 *            )
 * @param array $pipe
 *            array( //返回信息数组(待扩充)
 *            'return_line' => 最终节点回归线(默认标记第一个最终节点的回归线)
 *            'parent' => 所有父级数据
 *            'child' => 所有子级数据（最后一级子集）
 *            )
 * @return string $result 结果模版
 *         @time 2015-05-22
 * @author 秦晓武
 */
function tree_to_show($tree, $set = array(), &$pipe = array())
{
    if (! is_array($tree) || ! is_array($set) || ! count($tree)) {
        return '';
    }
    
    /* 初始化设置 */
    $default_set = array(
        'show' => 'title',
        'field' => array(
            'self' => 'id',
            'parent' => 'pid',
            'relation' => ''
        ),
        'function' => array(
            'replace_self' => '',
            'replace_parent' => ''
        ),
        'node' => '0',
        'level' => '-1',
        'limit' => '999',
        'disabled' => '-1',
        'prefix' => '|- ',
        'add_prefix' => '&nbsp;&nbsp;&nbsp;',
        'return_line' => array()
    );
    $set = array_overlay($default_set, $set);
    $default_pipe = array(
        'return_line' => array(
            'node' => - 1,
            'data' => array()
        ),
        'parent' => array(),
        'child' => array()
    );
    $pipe = array_overlay($default_pipe, $pipe);
    $field_self = $set['field']['self'];
    $field_parent = $set['field']['parent'];
    $result = '';
    /* 循环生成结构 */
    foreach ($tree as $key => $row) {
        $temp_set = $set;
        $temp_set['level'] += 1;
        if ($temp_set['level'] > $set['limit'])
            continue;
        if ($temp_set['level'] > 0) {
            $temp_set['prefix'] = $set['add_prefix'] . $set['prefix'];
        }
        if (! is_array($row['child']) || ! count($row['child'])) {
            $f = $set['function']['replace_self'];
            $pipe['child'][$row['data'][$field_self]] = $row['data'];
        } else {
            $temp_set['node'] = $key;
            $row['data']['tpl_child'] = tree_to_show($row['child'], $temp_set, $pipe);
            $f = $set['function']['replace_parent'];
            $pipe['parent'][$row['data'][$field_self]] = $row['data'];
        }
        switch ($pipe['return_line']['node']) {
            case - 1:
            case $key:
                $pipe['return_line']['node'] = isset($row['data'][$field_parent]) ? $row['data'][$field_parent] : 0;
                $pipe['return_line']['data'][] = $row['data'];
                $temp_set['return_line'][] = $key;
                $set['return_line'][] = $key;
                break;
            default:
                ;
        }
        $result .= call_user_func_array($f, array(
            $row['data'],
            $temp_set
        ));
    }
    return $result;
}

/**
 * 数组变成下拉框(无限级)
 * 
 * @param array $data
 *            原始数组（数据库查出的结果集）
 * @param int $id
 *            当前菜单ID
 * @param array $set
 *            配置数组(参照tree_to_show函数)
 * @return string $result 下拉框的option
 *         @time 2015-05-22
 * @author 秦晓武
 *        
 */
function array_to_select($data = array(), $id = '-2', $set = array())
{
    $tree = array_to_tree($data, $set);
    return tree_to_select($tree, $id, $set);
}

/**
 * 树型变成下拉框(无限级)
 * 
 * @param array $tree
 *            树型数组（data为父级数据，child为子级数据）
 * @param int $id
 *            当前菜单ID
 * @param array $set
 *            配置数组(参照tree_to_show函数)
 * @return string $result 下拉框的option
 *         @time 2015-05-22
 * @author 秦晓武
 *        
 */
function tree_to_select($tree = array(), $id = '-2', $set = array())
{
    $set['now'] = is_null($id) ? array() : array(
        $id
    );
    $set['function']['replace_self'] = 'select_self';
    $set['function']['replace_parent'] = 'select_parent';
    $result = tree_to_show($tree, $set);
    return $result;
}

/**
 * 数组变成下拉框，子级回调函数
 * 
 * @param array $row
 *            当前数据
 * @param array $set
 *            配置数组(参照tree_to_show函数)
 * @return string $result 结果模版
 *         @time 2015-05-22
 * @author 秦晓武
 *        
 */
function select_self($row, $set = array())
{
    $tpl = '<option data-level="%level" data-parent="%parent" value="%id" %disabled %selected>%title</option>';
    if (in_array($row[$set['field']['self']], $set['now'])) {
        $replace['%selected'] = 'selected="selected"';
    } else {
        $replace['%selected'] = '';
    }
    if ($set['level'] < $set['disabled']) {
        $replace['%disabled'] = 'disabled="disabled"';
    } else {
        $replace['%disabled'] = '';
    }
    $replace['%id'] = $row[$set['field']['self']];
    $replace['%parent'] = '';
    if (isset($row[$set['field']['parent']])) {
        $replace['%parent'] = $set['field']['relation'] ? $row[$set['field']['relation']] : $row[$set['field']['parent']];
    }
    $replace['%level'] = $set['level'];
    $replace['%title'] = $set['prefix'] . $row[$set['show']];
    foreach ($replace as $k => $v) {
        $replace_a[] = $k;
        $replace_b[] = $v;
    }
    return str_replace($replace_a, $replace_b, $tpl);
}

/**
 * 数组变成下拉框，父级回调函数
 * 
 * @param array $row
 *            当前数据
 * @param array $set
 *            配置数组(@see tree_to_show)
 * @return string 结果模版
 *         @time 2015-05-22
 * @author 秦晓武
 */
function select_parent($row, $set = array())
{
    $tpl = '<option data-level="%level" data-parent="%parent" value="%id" %disabled %selected>%title</option>%tpl_child';
    if (in_array($row[$set['field']['self']], $set['now'])) {
        $replace['%selected'] = 'selected="selected"';
    } else {
        $replace['%selected'] = '';
    }
    if ($set['level'] < $set['disabled']) {
        $replace['%disabled'] = 'disabled="disabled"';
    } else {
        $replace['%disabled'] = '';
    }
    $replace['%id'] = $row[$set['field']['self']];
    $replace['%parent'] = $set['field']['relation'] ? $row[$set['field']['relation']] : $row[$set['field']['parent']];
    $replace['%level'] = $set['level'];
    $replace['%title'] = $set['prefix'] . $row[$set['show']];
    $replace['%tpl_child'] = $row['tpl_child'];
    foreach ($replace as $k => $v) {
        $replace_a[] = $k;
        $replace_b[] = $v;
    }
    return str_replace($replace_a, $replace_b, $tpl);
}

/**
 * 数组变成面包屑(无限级)
 * 
 * @param array $data
 *            原始数组（数据库查出的结果集）
 * @param int $id
 *            当前菜单ID
 * @param array $set
 *            配置数组(参照tree_to_show函数)
 * @return string $result 下拉框的option
 *         @time 2015-05-22
 * @author 秦晓武
 *        
 */
function array_to_crumbs($data = array(), $id = '-2', $set = array())
{
    $tree = array_to_tree($data, $set);
    $pipe['return_line']['node'] = $id;
    $set['function']['replace_self'] = 'crumbs_self';
    $set['function']['replace_parent'] = 'crumbs_parent';
    return tree_to_show($tree, $set, $pipe);
}

/**
 * 生成子级结构(array_to_crumbs的回调函数)
 * 
 * @param array $row
 *            当前数据
 * @param array $set
 *            配置数组(参照tree_to_show函数)
 * @return string $result 结果模版
 *         @time 2015-05-22
 * @author 秦晓武
 *        
 */
function crumbs_self($row, $set = array())
{
    if (in_array($row[$set['field']['self']], $set['return_line'])) {
        return $row[$set['show']];
    }
}

/**
 * 生成父级结构(array_to_crumbs的回调函数)
 * 
 * @param array $row
 *            当前数据
 * @param array $set
 *            配置数组(参照tree_to_show函数)
 * @return string $result 结果模版
 *         @time 2015-05-22
 * @author 秦晓武
 *        
 */
function crumbs_parent($row, $set = array())
{
    if (in_array($row[$set['field']['self']], $set['return_line'])) {
        return $row[$set['show']] . ' &gt; ' . $row['tpl_child'];
    }
}

/**
 * 获取所有子级数据ID
 * @需求：通过ID获取所有子级数据
 * @流程：
 * 1.获取所有数据
 * 2.转换成树型
 * 3.在树内搜索
 * 4.转换子节点为数组
 * 5.取出子节点ID
 * 
 * @param array $all
 *            数据
 * @param string $id
 *            节点ID
 * @param boolean $self
 *            是否包含自身
 * @return string $result 结果数组
 *         @time 2015-05-22
 * @author 秦晓武
 * @example 传入0，返回[0,1,2,3]
 */
function get_all_child_ids($all = array(), $id = 0, $self = true)
{
    /* 转换成树型 */
    $tree = array_to_tree($all);
    /* 在树内搜索 */
    $sub_tree = search_in_tree($tree, $id);
    /* 转换子节点为数组 */
    $temp_tree = $self ? array(
        $sub_tree
    ) : $sub_tree['child'];
    $child = tree_to_array($temp_tree);
    /* 取出子节点ID */
    $child_ids = array_keys($child);
    return $child_ids;
}

/**
 * 获取所有父级数据ID
 * @需求：通过ID获取所有父级数据
 * 
 * @param array $data
 *            数据
 * @param string $id
 *            节点ID
 * @return string $result 结果数组
 *         @time 2016-11-17
 * @author 秦晓武
 * @example 传入4，返回[1,2,3]
 */
function get_all_parent_ids($data, $id)
{
    $result = array();
    while ($data[$id]['pid']) {
        $result[] = $data[$id]['pid'];
        $id = $data[$id]['pid'];
    }
    return $result;
}

/**
 * 写文件
 * @time 2015-05-19
 * 
 * @param string $file
 *            要写入的文件路径
 * @param array $data
 *            要写入的数据
 * @param string $mode
 *            文件打开模式，读写方式打开，将文件指针指向文件末尾。（fopen）
 * @author 秦晓武
 */
function write_file($file, $data, $mode = 'a+')
{
    if ($data == '') {
        return '';
    }
    $fp = fopen($file, $mode);
    if ($fp) {
        if (is_array($data)) {
            foreach ($data as $row) {
                $flag = fwrite($fp, $row);
                if (! $flag) {
                    return 'TIP_ERROR_INFO';
                }
            }
        } else {
            $flag = fwrite($fp, $data);
            if (! $flag) {
                return 'TIP_ERROR_INFO' . $data;
            }
        }
    } else {
        return "TIP_ERROR_INFO: " . $file;
    }
    fclose($fp);
}

/**
 * 记录日志
 * @time 2015-08-06
 * 
 * @param string $file_path
 *            文件目录（基于Runtime/Logs/Class）
 * @param string $content
 *            内容
 * @return boolean 状态
 * @author 秦晓武
 */
function write_log($file_path = "", $content = "")
{
    if (! $content || ! $file_path)
        return false;
    $dir = RUNTIME_PATH . 'Logs' . DIRECTORY_SEPARATOR . 'Class';
    if (! is_dir($dir) && ! mkdir($dir))
        return false;
    $dir = $dir . DIRECTORY_SEPARATOR . $file_path;
    if (! is_dir($dir) && ! mkdir($dir))
        return false;
    $filename = $dir . DIRECTORY_SEPARATOR . date("Ymd", time()) . '.log.php';
    $logs = include $filename;
    if ($logs && ! is_array($logs)) {
        unlink($filename);
        return false;
    }
    $logs[] = date("Y-m-d H:i:s") . ' : ' . $content;
    $str = "<?php \r\n return " . var_export($logs, true) . ";";
    if (! $fp = @fopen($filename, "wb")) {
        return false;
    }
    if (! fwrite($fp, $str))
        return false;
    fclose($fp);
    return true;
}

/**
 * 获取随机字符串
 * 
 * @param number $length
 *            获取随机字符串的长度,默认为6
 * @param string $strPol
 *            备选字符组成的字符串,默认为A-Za-z0-9;
 * @return string 获取的随机字符串
 * @author 李东
 *         @time 2015-11-18
 */
function get_rand_char($length = 6, $strPol = '')
{
    $str = '';
    $strPol = $strPol == '' ? "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz" : $strPol;
    $max = strlen($strPol) - 1;
    for ($i = 0; $i < $length; $i ++) {
        $str .= $strPol[rand(0, $max)]; // rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }
    return $str;
}

/**
 * IP转地址
 * 
 * @param string $p
 *            IP
 *            @time 2015-08-15
 * @author 秦晓武
 */
function ip_to_location($p = '127.0.0.1')
{
    // 导入IpLocation类
    $ip = new \Org\Net\IpLocation();
    $area = $ip->getlocation($p);
    $location = iconv("gbk", "utf-8", $area['country']);
    if ($location == 'IANA') {
        return "本地登录";
    } else {
        return $location;
    }
}

/**
 * 字符串截取，支持中文和其他编码
 * 
 * @param string $str
 *            需要转换的字符串
 * @param int $start
 *            开始位置
 * @param int $length
 *            截取长度
 * @param string $charset
 *            编码格式
 * @param string $suffix
 *            截断显示字符
 * @return string
 */
function msubstr($str = '', $start = 0, $length = 1, $charset = "utf-8", $suffix = '...')
{
    $str = trim(strip_tags($str));
    if (mb_strlen($str, $charset) <= $length)
        return $str;
    if (function_exists("mb_substr")) {
        $slice = mb_substr($str, $start, $length, $charset);
    } elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    return $slice . $suffix;
}

/**
 * 截取摘要的主要函数
 * 
 * @param $text 文本内容            
 * @param $length 截取长度            
 * @param $allowd_tag 允许标签            
 * @param $suffix 省略符            
 */
function msubstr_tag($text, $length = 150, $allowd_tag = true, $suffix = '...')
{
    /* 获取并整理所有内容 */
    $text = trim(str_replace(']]>', ']]&gt;', $text));
    if ($length > mb_strlen(strip_tags($text), 'utf-8')) {
        $suffix = '';
    }
    /* 获取标签 */
    $allowd_tag = $allowd_tag ? $allowd_tag : '<a><b><blockquote><br><cite><code><dd><del><div><dl><dt><em><h1><h2><h3><h4><h5><h6><i><li><ol><p><pre><span><strong><ul>';
    
    /* 去除标签 */
    $text = strip_tags($text, $allowd_tag);
    /* 去除&nbsp; */
    $text = strtr($text, [
        '&nbsp;' => chr(0xC2) . chr(0xA0)
    ]);
    $text = trim($text, chr(0xC2) . chr(0xA0) . ' ');
    /* 计算字数，截取摘要 */
    $num = 0;
    $in_tag = false;
    for ($i = 0; $num < $length || $in_tag; $i ++) {
        if (mb_substr($text, $i, 1) == '<')
            $in_tag = true;
        elseif (mb_substr($text, $i, 1) == '>')
            $in_tag = false;
        elseif (! $in_tag)
            $num ++;
    }
    $text = trim(mb_substr($text, 0, $i, 'utf-8'));
    $text = force_balance_tags($text);
    return $text . $suffix;
}

/**
 * 标签自动补全（代码来源：wordpress）
 *
 * @since 2.0.4
 *       
 * @author Leonard Lin <leonard@acm.org>
 * @license GPL
 * @copyright November 4, 2001
 * @version 1.1
 * @todo Make better - change loop condition to $text in 1.2
 * @internal Modified by Scott Reilly (coffee2code) 02 Aug 2004
 *           1.1 Fixed handling of append/stack pop order of end text
 *           Added Cleaning Hooks
 *           1.0 First Version
 *          
 * @param string $text
 *            Text to be balanced.
 * @return string Balanced text.
 */
function force_balance_tags($text = '')
{
    $tagstack = array();
    $stacksize = 0;
    $tagqueue = '';
    $newtext = '';
    // 已知自关闭标签
    $single_tags = array(
        'area',
        'base',
        'basefont',
        'br',
        'col',
        'command',
        'embed',
        'frame',
        'hr',
        'img',
        'input',
        'isindex',
        'link',
        'meta',
        'param',
        'source'
    );
    // 标签可以直接嵌套在自己
    $nestable_tags = array(
        'blockquote',
        'div',
        'object',
        'q',
        'span'
    );
    
    // WP bug fix for comments - in case you REALLY meant to type '< !--'
    $text = str_replace('< !--', '<    !--', $text);
    // WP bug fix for LOVE <3 (and other situations with '<' before a number)
    $text = preg_replace('#<([0-9]{1})#', '&lt;$1', $text);
    
    while (preg_match("/<(\/?[\w:]*)\s*([^>]*)>/", $text, $regex)) {
        $newtext .= $tagqueue;
        
        $i = strpos($text, $regex[0]);
        $l = strlen($regex[0]);
        
        // clear the shifter
        $tagqueue = '';
        // Pop or Push
        if (isset($regex[1][0]) && '/' == $regex[1][0]) { // End Tag
            $tag = strtolower(substr($regex[1], 1));
            // if too many closing tags
            if ($stacksize <= 0) {
                $tag = '';
                // or close to be safe $tag = '/' . $tag;
            }            // if stacktop value = tag close value then pop
            elseif ($tagstack[$stacksize - 1] == $tag) { // found closing tag
                $tag = '</' . $tag . '>'; // Close Tag
                                          // Pop
                array_pop($tagstack);
                $stacksize --;
            } else { // closing tag not at top, search for it
                for ($j = $stacksize - 1; $j >= 0; $j --) {
                    if ($tagstack[$j] == $tag) {
                        // add tag to tagqueue
                        for ($k = $stacksize - 1; $k >= $j; $k --) {
                            $tagqueue .= '</' . array_pop($tagstack) . '>';
                            $stacksize --;
                        }
                        break;
                    }
                }
                $tag = '';
            }
        } else { // Begin Tag
            $tag = strtolower($regex[1]);
            
            // Tag Cleaning
            
            // If it's an empty tag "< >", do nothing
            if ('' == $tag) {
                // do nothing
            }            // ElseIf it presents itself as a self-closing tag...
            elseif (substr($regex[2], - 1) == '/') {
                // ...but it isn't a known single-entity self-closing tag, then don't let it be treated as such and
                // immediately close it with a closing tag (the tag will encapsulate no text as a result)
                if (! in_array($tag, $single_tags))
                    $regex[2] = trim(substr($regex[2], 0, - 1)) . "></$tag";
            }            // ElseIf it's a known single-entity tag but it doesn't close itself, do so
            elseif (in_array($tag, $single_tags)) {
                $regex[2] .= '/';
            }             // Else it's not a single-entity tag
            else {
                // If the top of the stack is the same as the tag we want to push, close previous tag
                if ($stacksize > 0 && ! in_array($tag, $nestable_tags) && $tagstack[$stacksize - 1] == $tag) {
                    $tagqueue = '</' . array_pop($tagstack) . '>';
                    $stacksize --;
                }
                $stacksize = array_push($tagstack, $tag);
            }
            
            // Attributes
            $attributes = $regex[2];
            if (! empty($attributes) && $attributes[0] != '>')
                $attributes = ' ' . $attributes;
            
            $tag = '<' . $tag . $attributes . '>';
            // If already queuing a close tag, then put this tag on, too
            if (! empty($tagqueue)) {
                $tagqueue .= $tag;
                $tag = '';
            }
        }
        $newtext .= substr($text, 0, $i) . $tag;
        $text = substr($text, $i + $l);
    }
    
    // Clear Tag Queue
    $newtext .= $tagqueue;
    
    // Add Remaining text
    $newtext .= $text;
    
    // Empty Stack
    while ($x = array_pop($tagstack)) {
        $newtext .= '</' . $x . '>'; // Add remaining tags to close
    }
    // WP fix for the bug with HTML comments
    $newtext = str_replace("< !--", "<!--", $newtext);
    $newtext = str_replace("<    !--", "< !--", $newtext);
    
    return $newtext;
}

/**
 * seo功能
 * 
 * @param string $title
 *            title
 * @param string $keywords
 *            keywords
 * @param string $description
 *            description
 */
function seo_edit($title, $keywords, $description)
{
    $title = str_replace('，', ',', $title);
    $keywords = str_replace('，', ',', $keywords);
    $description = strip_tags($description);
    $description = str_replace('，', ',', $description);
    C('title', $title);
    C('keywords', $keywords);
    C('description', $description);
}

/**
 * 封装curl,get方式
 * 
 * @param string $url
 *            地址
 * @return string 结果
 */
function curl_get($url)
{
    /* 初始化 */
    $ch = curl_init();
    /* 设置选项，包括URL */
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    /* 执行并获取HTML文档内容 */
    $output = curl_exec($ch);
    /* 释放curl句柄 */
    curl_close($ch);
    /* 返回获得的数据 */
    return $output;
}

/**
 * 封装curl,post方式
 * 
 * @param string $url
 *            地址
 * @param array $data
 *            数据数组
 * @return string 结果
 */
function curl_post($url, $data)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $return = curl_exec($ch);
    curl_close($ch);
    return $return;
}

/**
 * 获取当前客户端类型
 * 
 * @return string 客户端类型
 */
function get_agent()
{
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    switch(true){
        case stristr($agent, 'micromessenger'):
            return 'weixin';
        case stristr($agent, 'android'):
            return 'android';
        case stristr($agent, 'iphone'):
            return 'iphone';
        case stristr($agent, 'ipad'):
            return 'ipad';
        case stristr($agent, 'win'):
            return 'win';
        case stristr($agent, 'linux'):
            return 'linux';
        case stristr($agent, 'unix'):
            return 'unix';
        case stristr($agent, 'mac os'):
            return 'mac os';
    }
    return $agent;
}

/**
 * 判断远程文件图片是否存在
 * 
 * @param string $url
 *            服务地址
 * @return bool 判断结果 CURL有缓存效果
 *         @time 2015-09-16
 * @author 陆龙飞 <747060156@qq.com>
 *         URL ：'http://'.$_SERVER['SERVER_NAME'].'/'.__ROOT__.'/''Uploads/ImgTemp/xxx.jpg'
 */
function check_remote_file_exists($url)
{
    $curl = curl_init($url);
    /* 不取回数据 */
    curl_setopt($curl, CURLOPT_NOBODY, true);
    /* 发送请求 */
    $result = curl_exec($curl);
    $found = false;
    /* 如果请求没有发送失败 */
    if ($result !== false) {
        /* 再检查http响应码是否为200 */
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($statusCode == 200) {
            $found = true;
        }
    }
    curl_close($curl);
    
    return $found;
}

/**
 * 注册发送短信接口，文档http://sdk2.entinfo.cn:8060/webservice.asmx
 * 
 * @param string $mobile
 *            手机号
 * @param string $content
 *            内容
 */
function send_mobile_code($mobile, $content = '')
{
    $config = M('config')->getField('name,value');
    $code = rand(1000, 9999);
    session('mobile_code', $code);
    session('mobile', $mobile);
    if (! $content) {
        $content = str_replace('#code#', $code, $config['MESSAGE_CONTENT']);
    }
    $sn = $config['MESSAGE_NAME'];
    $password = $config['MESSAGE_PASSWORD'];
    $pwd = md5($sn . $password);
    $pwd = strtoupper($pwd);
    $content = urlencode($content);
    $url = "http://sdk2.entinfo.cn:8061/mdsmssend.ashx?sn=$sn&pwd=$pwd&mobile=$mobile&content=$content&ext=&stime=&rrid=&msgfmt=";
    $status = curl_get($url);
    return $status;
}

/**
 * 短信发送（基于阿里大于）
 * 
 * @param string $mobile
 *            手机号
 * @param string $code
 *            验证码
 * @param string $key
 *            模版KEY
 * @param string $member_id
 *            收信人ID
 * @return string 发送结果
 */
function send_sms($mobile = '13800000000', $code = '999999', $key = '', $member_id = 0)
{
    $template = get_info('sms_template', array(
        'code' => $key
    ));
    /* 短信记录 */
    $data['member_id'] = $member_id;
    $data['mobile'] = $mobile;
    $data['type'] = 1;
    $data['template'] = $key;
    $data['add_time'] = date('Y-m-d H:i:s');
    $data['state'] = 0;
    /* 调用发送类 */
    vendor('dayu.TopSdk');
    $c = new TopClient();
    $c->appkey = C('SMS_KEY'); 
    $c->secretKey = C('SMS_SECRET');
    $c->format = 'json';
    $req = new AlibabaAliqinFcSmsNumSendRequest();
    /* 回传信息 */
    $req->setExtend($data['member_id']);
    $req->setSmsType("normal");
    /* 短信签名 */
    $req->setSmsFreeSignName($template['sign']);
    /* 验证码 */
    $param['code'] = $code;
    /* 网站标识 */
    $param['product'] = $template['product'];
    $req->setSmsParam(json_encode($param));
    /* 手机号 */
    $req->setRecNum($data['mobile']);
    /* 短信模版 */
    $req->setSmsTemplateCode($key);
    $resp = $c->execute($req);
    $data['result'] = json_encode($resp);
    if ($resp->result) {
        $data['state'] = 1;
    }
    ;
    update_data('sms_record', array(), array(), $data);
    $result = json_decode($resp);
    return 1 && $resp->result;
}

/**
 * 系统邮件发送函数
 * 
 * @param $to 接收邮件者邮箱            
 * @param $name 接收邮件者名称            
 * @param $subject 邮件主题            
 * @param $body 邮件内容            
 * @param $attachment 附件列表            
 * @return boolean
 */
function send_mail($to, $name, $subject = '', $body = '')
{
    vendor('PHPMailer.class#phpmailer'); // 从PHPMailer目录导class.phpmailer.php类文件
    
    $mail = new PHPMailer(); // PHPMailer对象
    $mail->CharSet = 'UTF-8'; // 设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP(); // 设定使用SMTP服务
    $mail->SMTPDebug = 0;
    // $mail->SMTPDebug = 1; // 关闭SMTP调试功能
    // 1 = errors and messages
    // 2 = messages only
    $mail->SMTPAuth = true; // 启用 SMTP 验证功能
    // $mail->SMTPAuth = false; // 启用 SMTP 验证功能 如果为false则不用填写用户名密码也可以发送Email
    // $mail->SMTPSecure = 'ssl'; // 使用安全协议
    $mail->Host = C('SMTP_HOST'); // SMTP 服务器
    $mail->Port = C('SMTP_PORT'); // SMTP服务器的端口号
    $mail->Username = C('SMTP_USER'); // SMTP服务器用户名
    $mail->Password = C('SMTP_PASS'); // SMTP服务器密码
    
    $mail->SetFrom(C('FROM_EMAIL'), C('FROM_NAME'));
    
    $mail->FromName = C('FROM_NAME');
    $mail->From = C('FROM_EMAIL');
    
    $mail->Subject = $subject;
    $mail->MsgHTML($body);
    
    $mail->AddAddress($to, $name);
    
    $return_info = $mail->Send() ? true : $mail->ErrorInfo;
    
    return $return_info;
}

/**
 * 订单号生成方法
 * 
 * @return string 16位订单号
 *        
 * @author 李东<947714443@qq.com>
 *         @date 2016-03-21
 */
function build_order_no()
{
    return date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}

/**
 * 创建数据缓存
 * @time 2014-01-16
 * 
 * @author 郭文龙 <2824682114@qq.com>
 * @param
 *            $result
 * @param
 *            $cache_name
 * @param
 *            $by_filed
 */
function get_cache_data($result, $cache_name, $by_filed)
{
    if ($cache_name != '') {
        if (! F($cache_name)) {
            $cache_data = array();
            foreach ($result as $row) {
                if ($by_filed == '') {
                    $cache_data[$row['id']] = $row;
                } else {
                    $cache_data[$row[$by_filed]][] = $row;
                }
            }
            F($cache_name, $cache_data);
        }
        return F($cache_name);
    }
}

/**
 * 字节格式化 把字节数格式为 B K M G T 描述的大小
 * 
 * @param string $size
 *            文件大小
 * @param string $dec
 *            小数位数
 * @return string 格式化后结果
 */
function byte_format($size, $dec = 2)
{
    $a = array(
        "B",
        "KB",
        "MB",
        "GB",
        "TB",
        "PB"
    );
    $pos = 0;
    while ($size >= 1024) {
        $size /= 1024;
        $pos ++;
    }
    return round($size, $dec) . " " . $a[$pos];
}