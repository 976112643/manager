<?php

/**
 * 获取后台管理菜单
 * 康利民 2015-06-12
 */
function get_menu_list()
{
    if (session("menu_result"))
        return session("menu_result");
    /**
     * 如果是系统管理员拥有所有权限
     */
    $map = array();
    $member_id = session('member_id');
    /**
     * 如果是超级用户 即member_id==1，拥有所有权限
     */
    if ($member_id !== '1') {
        $info_admin = session('member_info');
        $info_role = session('member_role');
        $roles = array_merge(explode(',', $info_admin['rules']), explode(',', $info_role['menu_ids']));
        $map['id'] = array(
            'in',
            $roles
        );
    }
    $map['is_hid'] = '0';
    $menu_result = get_result('menu', $map, 'sort desc,id asc');
    /**
     * 把对应角色的功能菜单放入session
     */
    session("menu_result", $menu_result);
    return session("menu_result");
}

/**
 * 根据ID获取所有子集
 * 
 * @param $id 菜单id            
 * @return array 该菜单对应的子集菜单数组
 * @author 康利民 2015-06-12
 */
function get_child_menu_list($id = 0)
{
    $menu_result = get_menu_list();
    $result = array();
    foreach ($menu_result as $key => $value) {
        if ($id == $value['pid']) {
            $result[] = $value;
        }
    }
    return $result;
}

/**
 * 根据当前选中菜单返回头部对应操作按钮
 * 
 * @param $menu_id 当前选中菜单ID            
 * @param $pid pid
 *            参数，可选
 * @author 康利民 2015-06-16
 */
function get_top_btn($menu_id = 0, $pid = '')
{
    $child = get_child_menu_list($menu_id);
    $result = '';
    foreach ($child as $v) {
        /* 只显示启用的操作，如果为 1 或者3 就是ajax-post */
        if (! $v['is_hid'] && ($v['display_position'] == 1 || $v['display_position'] == 3)) {
            $class = str_replace('ajax-get', 'ajax-post', $v['class']);
            $result .= ' <a href="' . U($v['url'], I('get.')) . '" class="btn btn-sm ' . $class . '" target-form="ids" title="' . $v["title"] . '">' . $v["title"] . '</a> ';
        }
    }
    return $result;
}

/**
 * 根据当前选中菜单返回列表对应操作按钮
 * 自动隐藏当前无需进行的操作（比如已禁用的数据无需显示禁用按钮）
 * 
 * @param int $menu_id
 *            当前选中菜单ID
 * @param array $row
 *            当前数据数组
 * @param array $remove
 *            要移除的配置数组，格式参照$default
 * @author 秦晓武 2016-08-10
 */
function get_list_btn($menu_id, $row = array(), $remove = array())
{
    /* 默认移除配置 */
    $default = array(
        'is_hid' => array(
            '0' => U("enable"),
            '1' => U("disable")
        ),
        'recommend' => array(
            '1' => U("recommend"),
            '0' => U("no_recommend")
        )
    );
    /* 合并 */
    $set = array_merge($default, $remove);
    $url = array();
    /* 生成要移除的URL */
    foreach ($set as $key => $value) {
        if (isset($row[$key])) {
            if (is_array($value[$row[$key]])) {
                $url = array_merge($url, $value[$row[$key]]);
            } else {
                $url[] = $value[$row[$key]];
            }
        }
    }
    /* 遍历对应菜单id的子集菜单，获取所有操作 */
    $child = get_child_menu_list($menu_id);
    $result = '';
    foreach ($child as $v) {
        /* 只显示列表或全局操作 */
        if (! in_array($v['display_position'], array(
            2,
            3
        )) || $v['is_hid']) {
            continue;
        }
        $cur_url = U($v['url']);
        /* 屏蔽移除操作 */
        if (in_array($cur_url, $url)) {
            continue;
        }
        $class = str_replace('ajax-post', 'ajax-get', $v['class']);
        $array = array_merge(I('get.'), array(
            'ids' => $row['id']
        ));
        unset($array['p']);
        $result .= ' <a href="' . U($v['url'], $array) . '" class="btn btn-xs ' . $class . '" title="' . $v["title"] . '">' . $v['title'] . '</a> ';
    }
    echo $result;
}

/**
 * 添加日志
 * 
 * @param string $id
 *            操作记录ID
 *            @time 2016-07-08
 * @author 秦晓武
 */
function action_log($id = 0)
{
    /* 当前操作 */
    $menu_url = MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME;
    /* 所有菜单 */
    $menu_result = get_no_del('menu');
    $action = array(
        'id' => '',
        'title' => '',
        'level' => ''
    );
    $title = array(
        'url' => '',
        'title' => ''
    );
    /* 匹配信息 */
    foreach ($menu_result as $k => $row) {
        if ($menu_url == $row['url']) {
            $action = $row;
        }
    }
    /* 获取父级 */
    foreach ($menu_result as $k => $row) {
        if (isset($action['pid']) && $action['pid'] == $row['id']) {
            $title = $row;
        }
    }
    $data['url'] = $title['url'];
    $data['username'] = session('username');
    $data['menu_id'] = $action['id'];
    $data['title'] = $title['title'] . ' > ' . $action['title'];
    $data['title'] = array_to_crumbs($menu_result, $action['id']);
    $data['action'] = '浏览';
    if ($action['level'] == '3') {
        $data['action'] = $action['title'];
        if ($id) {
            $data['action'] .= ' | ' . $id;
        }
    }
    $data['ip'] = get_client_ip();
    $data['admin_id'] = session('member_id');
    $data['add_time'] = date('Y-m-d H:i:s');
    if (! $data['url']) {
        switch ($menu_url) {
            case 'Backend/Base/Public/login':
                $data['url'] = 'Backend/Base/Public/login';
                $data['title'] = '';
                $data['menu_id'] = 0;
                $data['action'] = '登录';
                break;
            default:
                $data['url'] = $menu_url;
                ;
        }
    }
    M('action_log')->add($data);
}
/**
 * 搜索时间设置
 * @param string $field 需要查询的时间字段
 * @param string $type 处理的时间格式 true为int类型,false为date类型
 * @time 2016-8-04
 * @author 陶君行<Silentlytao@outlook.com>
 */
function search_time( $field = 'add_time',$type = true){
    $map = [];
    $start_time = str_replace('+',' ',I('start_time'));
    $end_time   = str_replace('+',' ',I('end_time'));

    if($type){
        $start_time = strtotime($start_time);
        $end_time   = strtotime($end_time);
    }
    if(!empty($start_time)){
        $map[$field][] = array('egt',$start_time);
    }
    if(!empty($end_time)){
        $map[$field][] = array('elt',$end_time);
    }
    if(isset($map[$field])) return $map[$field];
}
/**
 * 搜索时间区域html
 * @time 2016-10-21
 * @author 陶君行<Silentlytao@outlook.com>
 */
function search_time_html()
{
    $start_time = str_replace('+',' ',I('start_time'));
    $end_time   = str_replace('+',' ',I('end_time'));
    $img_path = C('TMPL_PARSE_STRING')['__IMG__'];
    echo <<<EOT
                <div class="form-group" style="position: relative;z-index:0;">
					<input type="text" placeholder="开始时间"
					      class="form-control input-sm" name="start_time"
					      value="{$start_time}" id="start_time"
					       onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',maxDate:'%y-%M-%d %HH:%mm:%ss'})" />
					      <img src="{$img_path}/calendar.png" height="16" style="position: absolute;z-index:0;right: 5px;top:5px;" />
				</div>
				<div class="form-group" style="position: relative;z-index:0;">
					<input type="text" placeholder="结束时间"
					      class="form-control input-sm" name="end_time"
					      value="{$end_time}" id="end_time"
					       onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',maxDate:'%y-%M-%d %HH:%mm:%ss'})" />
					      <img src="{$img_path}/calendar.png" height="16" style="position: absolute;z-index:0;right: 5px;top:5px;" />
				</div>
EOT;
}