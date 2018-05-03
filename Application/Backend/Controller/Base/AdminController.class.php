<?php
namespace Backend\Controller\Base;

use Common\Controller\CommonController;
use Common\Help\ExcelHelp;
/**
 * 后台总父类
 */
class AdminController extends CommonController
{

    /**
     * 表名
     * 
     * @var string
     */
    protected $table = '';

    /**
     * 全局加载
     * @time 2014-12-26
     * 
     * @author 郭文龙 <2824682114@qq.com>
     */
    protected function __autoload()
    {
        if (! session('member_id')) {
            header("location:" . __ROOT__ . "/Backend");
        }
        /**
         * 获取菜单
         */
        $menu_result = get_menu_list();
        $menu_arr = array_column($menu_result, 'url');
        /**
         * 添加无权限控制的页面
         */
        $menu_arr[] = MODULE_NAME . '/' . CONTROLLER_NAME . '/' . 'uploadPicture';
        $menu_arr[] = MODULE_NAME . '/' . CONTROLLER_NAME . '/' . 'uploadFile';
        $menu_arr[] = MODULE_NAME . '/' . CONTROLLER_NAME . '/' . 'uploadFileAPK';
        $menu_arr[] = MODULE_NAME . '/' . CONTROLLER_NAME . '/' . 'delTempFile';
        $menu_arr[] = 'Backend/Base/Index/index';
        $menu_arr[] = 'Backend/Index/Index';
        $menu_arr[] = 'Backend/Demo/Index/index';
        $menu_arr[] = 'Backend/Base/Info/edit';
        
        $url = MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME;
        if (! in_array($url, $menu_arr) && ! stristr($url, "ueditor") && ! stristr($url, "ajaxDelete_")) {
            $this->error('未授权的访问');
        }
        /* 预定义模版变量，避免Noitce错误 */
        $data = array(
            'search' => '',
            'start_date' => '',
            'stop_date' => '',
            'page' => ''
        );
        if ($this->table) {
            foreach (M($this->table)->getDbFields() as $key) {
                $data[$key] = '';
            }
        }
        $data['menu_result'] = array_to_tree($menu_result);
        $this->assign($data);
        $this->_init();
        if (! IS_POST && ! IS_AJAX) {
            // action_log();
        }
    }

    /**
     * 初始化，用于继承
     */
    protected function _init()
    {}

    /**
     * 分割线，操作处理函数
     * 
     * @author 秦晓武
     *         @time 2016-06-14
     */
    private function ________ACTION________()
    {}

    /**
     * 处理排序
     * 
     * @param array $sort
     *            需要排序的字段数组
     * @param string $order
     *            默认排序
     * @return string 处理后排序
     * @author 秦晓武
     *         @time 2016-06-14
     */
    function get_order($sort = array(), $order = 'id desc')
    {
        $data = array();
        /* 遍历需要排序的字段数组 */
        foreach ($sort as $field) {
            $data[$field]['class'] = '';
            $by = 'asc';
            /* 匹配URL参数处理对应URL及CLASS */
            if (I('get.order') == $field) {
                $by = I('get.by') == 'asc' ? 'desc' : 'asc';
                $data[$field]['class'] = I('get.by');
                $order = I('get.order') . ' ' . I('get.by');
            }
            $data[$field]['url'] = U('', array_merge(I('get.'), array(
                'order' => $field,
                'by' => $by
            )));
        }
        $this->assign('sort', $data);
        return $order;
    }

    /**
     * 启用
     */
    function enable()
    {
        $this->change_field_value('is_hid', 0);
    }

    /**
     * 禁用
     */
    function disable()
    {
        $this->change_field_value('is_hid', 1);
    }

    /**
     * 不推荐
     */
    function no_recommend()
    {
        $this->change_field_value('recommend', 0);
    }

    /**
     * 推荐
     */
    function recommend()
    {
        $this->change_field_value('recommend', 1);
    }

    /**
     * 删除
     */
    function del()
    {
        $this->change_field_value('is_del', 1);
    }

    /**
     * 更新单个字段，可批量，主键ID，对应参数IDS
     * 
     * @param string $field
     *            字段名
     * @param string $key_field
     *            值
     *            @time 2015-06-14
     * @author 秦晓武
     */
    protected function change_field_value($field = '', $value = null)
    {
        if (! $field) {
            return '';
        }
        $ids = I('ids');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $value = is_null($value) ? intval(I($field)) : $value;
        if (is_array($ids)) {
            $_POST = array(
                $field => $value
            );
            $map[M($this->table)->getPk()] = array(
                'in',
                $ids
            );
            $result = update_data($this->table, array(), $map);
        } else {
            $ids = intval($ids);
            $_POST = array(
                M($this->table)->getPk() => $ids,
                $field => $value
            );
            $result = update_data($this->table);
            if (! is_numeric($result)) {
                $this->error($result);
            }
        }
        if (isset($_GET['ids'])) {
            unset($_GET['ids']);
        }
        $this->success('操作成功', U('index', I('get.')));
    }

    /**
     * 更新后的回调函数
     * 
     * @param
     *            string 当前ID
     * @return string 更新结果
     *         @time 2015-06-14
     * @author 秦晓武
     */
    public function call_back_change($id = '')
    {
        /* 写入日志 */
        action_log($id);
        /* 清除缓存 */
        $this->clean_cache($id);
        return true;
    }

    /**
     * 清除缓存
     * 
     * @param
     *            string 当前ID
     *            @time 2015-06-14
     * @author 秦晓武
     */
    public function clean_cache($id = '')
    {
        F($this->table . '_no_del', null);
        F($this->table . '_no_hid', null);
    }

    /**
     * 通用格式转换
     * @time 2016-9-18
     * @param array $row 内容数组
     * @author 秦晓武
     */
    public function format_value(&$row)
    {
        if (isset($row['is_hid'])) {
            $row['is_hid'] = $row['is_hid'] ? '隐藏' : '显示';
        }
        if (isset($row['recommend'])) {
            $row['recommend'] = $row['recommend'] ? '是' : '否';
        }
    }
    /**
     * 生成html
     * @param  [type]  $list     [数据数组]
     * @param  integer $id       [头ID]
     * @param  string  $name     [表单name]
     * @param  string  $text     [表单提示信息]
     * @param  string  $class    [表单类]
     * @param  string  $disabled [是否禁用]
     * @return [type]            [description]
     */
    public function html($list,$id = 0, $name = 'category_id',$text='请选择分类',$class = 'form-control select2 ', $disabled = '') {
        $list = list_to_tree($list);
        $html = '<select name="'.$name.'" class="'.$class.'" datatype="n" errormsg="'.$text.'" nullmsg="'.$text.'">';
        $html .= '<option value="">|-- '.$text.'</option>';
        foreach($list as $value) {
            $selected = $value['id'] == $id ? ' selected="selected"' : '';
            $html .= '<option value="'.$value['id'].'"'.$selected.$disabled.'>|-- '.$value['title'].'</option>';
            foreach($value['_child'] as $v) {
                $selected = $v['id'] == $id ? ' selected="selected"' : '';
                $html .= '<option value="'.$v['id'].'"'.$selected.$disabled.'>|--|-- '.$v['title'].'</option>';
                foreach($v['_child'] as $row) {
                    $selected = $row['id'] == $id ? ' selected="selected"' : '';
                    $html .= '<option value="'.$row['id'].'"'.$selected.'>|--|--|-- '.$row['title'].'</option>';
                }
            }
        }
        $html .= '</select>';
        return $html;
    }
    /**
     * 基础设置搜索
     * @param   $[filed] [搜索的字段名]
     * @time 2016-10-21
     * @author 陶君行<Silentlytao@outlook.com>
     */
    public function default_map($filed = '',$addtime='add_time',$type = false,$is_mil=false)
    {
        /**关键词*/
        $map = [];
        $keywords = trim(I('keywords/s'));
        /**状态*/
        $is_hid   = trim(I('is_hid/s'));
        if($keywords){
            $map[$filed] = array( 'like' ,'%' . $keywords . '%');
        }
        if($is_hid){
            $map['is_hid'] = $is_hid;
        }elseif($is_hid == '0'){
            $map['is_hid'] = intval($is_hid);
        }
        /** 时间 */
        $time = $is_mil?search_time_mil($addtime,$type):search_time($addtime,$type);
        if($time)   $map[$addtime] = $time;
        return $map;
    }
    /**
     * 导出excel
     * @time 2016-10-21
     * @author 陶君行<Silentlytao@outlook.com>
     */
    public function export_data()
    {
        $data['result'] = $this->get_list('all');
        $data['sheetName'] = $this->export_name;
        $class = new ExcelHelp();
        $class->create_excel($this->get_export_config(), $data);
    }
    /**
     * 获取导出配置
     */
    protected function get_export_config()
    {
         
    }
    /**
     * 获取数据集
     * @param   $[method] [获取方法]
     * @time 2016-10-28
     * @author 陶君行<Silentlytao@outlook.com>
     */
    protected function get_list($method = 'page')
    {
    
    }
    /**
     * 获取省市区
     * @param   $[info] [省市区信息]
     */
    protected function get_area_str(& $info)
    {
        $address = get_no_del('area');
        $province = $info['province'] = $address[$info['province_id']]['title'];
        $city = $info['city'] = $address[$info['city_id']]['title'];
        $zone = $info['zone'] = $address[$info['zone_id']]['title'];
        $info['all_address'] = $province . $city . $zone .$info['address'];
        return $info;
    }
    /**
     * 空操作
     * @time 2016-11-3
     * @author 陶君行<Silentlytao@outlook.com>
     */
    public function _empty()
    {
        $this->display('Public:404');
    }
    
    /**
     * ajax删除代码图片
     * @param  array  $path  [需要删除字段定义]
     * @param  string $table [表名]
     * @return [type]        [description]
     */
    protected function ajax_del($path = array('cover'),$table = '')
    {
        $posts = I ( "post." );
        $table = $table ? $table : $this->table;
        $info = get_info ( $table, array ("id" => $posts ['id']));
        if(in_array($posts['name'],$path)){
            $name = $info[$posts['name']];
            if(file_exists($name)){
                del_thumb($name);
                $info[$posts['name']] = '';
                update_data($table,'','',$info);
                $this->success('删除成功');
            }else{
                $info[$posts['name']] = '';
                update_data($table,'','',$info);
                $this->success ( "文件不存在，删除失败，数据被清空" );
            }
        }
    }
    
    
     /**
      * 统计数据
      * @author 鲍海
      * @param $table_name 表名  $table_name
      * @param $field 统计字段  $field
      * @param $map 筛选条件 $map
      * @param $date 输出的模板变量名称 （列表）
      * @param $list_name 输出的模板变量名称 （总数）
      * @param $date_field 时间筛选字段
      * @param $count_name 输出的模板变量名称 （总数）
      */
     protected function count_data($table_name,$field,$map,$date,$list_name='member',$time_field='add_time',$time_field_type='',$count_name=''){
         switch ($time_field_type) {
             case 'int':
                 $map[$time_field]=array('between',strtotime(reset($date).' 00:00:00').','.strtotime(end($date).' 23:59:59'));
                 break;
             case 'datetime':
                 $map[$time_field]=array('between',reset($date).' 00:00:00'.','.end($date).' 23:59:59');
                 break;
             case 'date':
                 $map[$time_field]=array('between',reset($date).','.end($date));
                 break;
             default:
                 $map[$time_field]=array('between',reset($date).','.end($date));
                 break;
         }
         if($field){
             $res=M($table_name)->field($time_field.','.$field)->where($map)->select();
         }else{
             $res=M($table_name)->field($time_field)->where($map)->select();
         }
         dump($res);
         $num = count($date);
         for ($i=0;$i<$num;$i++) {
             $_start = strtotime($date[$i]);
             if($i==$num-1){
                 /*要向时间后移一个单位，但不知道具体单位，所以设置较大的值*/
                 $_end=strtotime($date[$i+1]."+1 ".$this->step);
             }else{
                 $_end=strtotime($date[$i+1]);
             }
             $_member[$i]=array();
             if($res){
                 foreach ($res as $k => $v) {
                     if($time_field_type=='int'){
                         if($_start <= $v[$time_field] &&  $v[$time_field] <$_end){
                             $_member[$i][]=$v;
                         }
                     }else{
                         if($_start <= strtotime($v[$time_field]) && strtotime( $v[$time_field] )<$_end){
                             $_member[$i][]=$v;
                         }
                     }
                 }
             }
             if($field){
                 $user_list['member'][] = array_sum(array_column($_member[$i],$field));
             }else{
                 $user_list['member'][]=count($_member[$i]);
             }
         }
         if($count_name){
             if($field){
                 $count = array_sum(array_column($res,$field));
                 $data[$count_name]  = $count ? $count : 0;
             }else{
                 $data[$count_name]  = count($res) ? count($res) : 0;
             }
         }
         $data[$list_name]=json_encode($user_list['member']);
         return $data;
     }
    
    
}

