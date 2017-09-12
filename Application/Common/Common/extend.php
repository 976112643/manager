<?php

/**扩展函数库*/
    /**
     * 获取MD5登录密码
     * @param  string $pwd  [密码]
     * @param  string $salt [追加字符]
     * @return [type]       [description]
     */
    function get_md5_password($pwd = '123456',$salt = '')
    {
        return md5( md5( $pwd ) . $salt);
    }
    /**
     * 批量更新数据
     *  @param $saveWhere ：想要更新主键ID数组
     *  @param $saveData    ：想要更新的ID数组所对应的数据
     *  @param $tableName  : 想要更新的表明
     *  @param $saveWhere  : 返回更新成功后的主键ID数组
     *  @return string
     * */
    function saveAll($saveWhere, & $saveData,$tableName)
    {
        if($saveWhere==null||$tableName==null)
            return false;
        //获取更新的主键id名称
        $key = array_keys($saveWhere)[0];
        //获取更新列表的长度
        $len = count($saveWhere[$key]);
        $flag=true;
        $model = isset($model)?$model:M($tableName);
        //开启事务处理机制
        $model->startTrans();
        //记录更新失败ID
        $error=[];
        for($i=0;$i<$len;$i++){
            //预处理sql语句
            $isRight=$model->where($key.'='.$saveWhere[$key][$i])->save($saveData[$i]);
            if($isRight==0){
                //将更新失败的记录下来
                $error[]=$i;
                $flag=false;
            }
            //$flag=$flag&&$isRight;
        }
        if($flag ){
            //如果都成立就提交
            $model->commit();
            return $saveWhere;
        }elseif(count($error)>0&count($error)<$len){
            //先将原先的预处理进行回滚
            $model->rollback();
            for($i=0;$i<count($error);$i++){
                //删除更新失败的ID和Data
                unset($saveWhere[$key][$error[$i]]);
                unset($saveData[$error[$i]]);
            }
            //重新将数组下标进行排序
            $saveWhere[$key]=array_merge($saveWhere[$key]);
            $saveData=array_merge($saveData);
            //进行第二次递归更新
            saveAll($saveWhere,$saveData,$tableName);
            return $saveWhere;
        }
        else{
            //如果都更新就回滚
            $model->rollback();
            return false;
        }
    }


    /**
     * @function 计算两个日期相差多少天，多少小时，多少分钟
     * @param int $begin_time
     * @param int $end_time
     * 调用方法:timediff( strtotime( 开始日期 ), strtotime( 结束日期  ) )
     */
    function timediff( $begin_time, $end_time ) 
    { 
        if ( $begin_time < $end_time ) { 
            $starttime = $begin_time; 
            $endtime = $end_time; 
        } else { 
            $starttime = $end_time; 
            $endtime = $begin_time; 
        } 
        $timediff = $endtime - $starttime; 
        $days = intval( $timediff / 86400 ); 
        $remain = $timediff % 86400; 
        $hours = intval( $remain / 3600 ); 
        $remain = $remain % 3600; 
        $mins = intval( $remain / 60 ); 
        $secs = $remain % 60; 
        $res = array( "day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs );         
        return $res; 
    } 

    /**
     * 更新每天用户注册量
     * @author 鲍海
     * @time 2017-03-28
     */
    function count_member_day($type = '10'){
        $map = [];
        $map['type'] = '10';
        $map['add_time'] = date('Y-m-d',time());
        $info = get_info('member_count',$map);

        if($info['id']){
            M('member_count')->where('id='.$info['id'])->setInc('count',1);
        }else{
            $data['type'] = '10';
            $data['count'] = 1;
            $data['add_time'] = date('Y-m-d',time());
            update_data('member_count',[],[],$data);
        }
    }


    /**
     * 请求高德地图接口获取经纬度数据
     * @author 鲍海
     * @time 2017-03-27
     */
    function get_lat_log($address){
        /*请求高德地图接口获取经纬度数据*/
        $url = 'http://restapi.amap.com/v3/geocode/geo?key='.C('GEO_KEY').'&address='.$address;
        $add_info = file_get_contents($url);
        $add_info = json_decode($add_info,true);
        $local = $add_info['geocodes']['0'];
        $tude = array();
        if(!empty($local)){
            $tude = explode(',',$local['location']);
        }
        return $tude;
    }
    
    /** 
     *  @desc 根据两点间的经纬度计算距离 
     *  @param float $lat 纬度值 
     *  @param float $lng 经度值 
     */  
    function getDistance($lat1, $lng1, $lat2, $lng2)  {  
        $earthRadius = 6371000; //approximate radius of earth in meters  
        $lat1 = ($lat1 * pi() ) / 180;  
        $lng1 = ($lng1 * pi() ) / 180;  
       
        $lat2 = ($lat2 * pi() ) / 180;  
        $lng2 = ($lng2 * pi() ) / 180;  
        $calcLongitude = $lng2 - $lng1;  
        $calcLatitude = $lat2 - $lat1;  
        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);    
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));  
        $calculatedDistance = $earthRadius * $stepTwo;  
        if($calculatedDistance>500){
            return round($calculatedDistance/1000,1).'km';  
        }else{
            return round($calculatedDistance,1).'m';
        }
        //return round($calculatedDistance);  
    }

    /**
     * 订单销售记录
     */
    function saller_order_log($type,$order_id,$amount,$order_money,$order_no)
    {
        $_data = array(
            'type'=>$type,
            'order_id'=>$order_id,
            'amount'=>$amount,
            'shop_id'=>$shop_id,
            'shop_amount'=>$shop_amount,
            'order_money'=>$order_money,
            'order_no'=>$order_no,
            'add_time'=>date('Y-m-d H:i:s',NOW_TIME)
            );
        M('sales_log')->add($_data);
    }

    /**
     * 计算utf-8字符长度
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    function strlen_utf8($str){
        $i = 0;
        $count = 0;
        $len = strlen($str);
        while ($i < $len)
        {
            $chr = ord($str[$i]);
            $count++;
            $i++;
            if ($i >= $len)
            {
                break;
            }
            if ($chr & 0x80)
            {
                $chr <<= 1;
                while ($chr & 0x80)
                {
                    $i++;
                    $chr <<= 1;
                }
            }
        }
        return $count;
    }


    /**
     * 获取个人中心未读消息个数
     */
    function get_message_no_read_num($member_id){
        $result = get_result('message_receive',array('member_id'=>$member_id,'is_read'=>0));
        return count($result)?count($result):0;
    }

    
    /**
     * 订单记录操作记录
     */
    function order_log($order_id,$member_id,$admin_id,$remark,$order_detail_remark='')
    {   
        $_data = array(
            'order_id'=>$order_id,
            'member_id'=>$member_id,
            'admin_id'=>$admin_id,
            'remark'=>$remark,
            'add_time'=>date('Y-m-d H:i:s',NOW_TIME),
            'order_detail_remark'=>$order_detail_remark,
            );
        M('order_log')->add($_data);
    }

    /*
     * 发送个人消息消息接口
     * $parma $type ,消息类型
     *        $to_uid ,接收消息用户id
              $from_id 发送消息用户id
              $data 消息内容
              $send_num 是否发送多条 true 是 false 否
     * return 1:成功  2: 参数错误，缺少type参数，或者用户form_id to_uid
                3:接收消息的用户不存在 4：消息内容为空 5: 消息入库失败           
     **/

    function send_message_one($type='',$from_id='',$to_uid='',$data=array(),$send_num = false){
        if(!$type||!is_numeric($to_uid)||!is_numeric($from_id)) return 2;
        $info = get_info('message_send',array('type'=>$type));
        if($info['id']>0){
            $data['title'] = $info['title'];
            $data['content'] = $info['content'];
            $data['send_id'] = $info['id'];
            $data['send_name'] = '系统管理员';
        }
        if(!$send_num){
            if(!$data['title']&&!$data['content']) return 4;
        }
        $info = get_info('member',array('id'=>$to_uid));
        if(!$info) return 3;
        if(!$send_num){
            $datas = array(
                'send_id'   =>  $data['send_id'],
                'send_name' =>  $data['send_name'],
                'type'      => $type,
                'from_id'   => $from_id,
                'member_id' => $to_uid,
                'content'   => $data['content'],
                'title'   => $data['title'],
                'addtime' => time()
            );
            $res = update_data('message_receive',array(),array(),$datas);
            if(is_numeric($res)){
                return 1;
            }else{
                return 5;
            }
        }else{
            foreach($data as $v){
                $datas[] = array(
                    'send_id'   =>  $data['send_id'],
                    'send_name' =>  $data['send_name'],
                    'type'      => $type,
                    'from_id'   => $from_id,
                    'member_id' => $to_uid,
                    'content'   => $v['content'],
                    'title'   => $v['title'],
                    'addtime' => time()
                );
            }
            $res = M('message_receive')->addALL($datas);
            if(is_numeric($res)){
                return 1;
            }else{
                return 5;
            }
        }
    }

/**
 * 头像地址处理，第三方头像与本地头像冲突
 * @param  [type] $head_img [description]
 * @return [type]           [description]
 */
function head_image($head_img){
    if(!$head_img){
        $_img = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/Public/Static/img/avatar_100.jpg';
    }
    if(strlen($head_img)>0){
        $_img = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/'.$head_img;
    }

    if(!file_exists($head_img)){
        $_img = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/Public/Static/img/avatar_100.jpg';
    }

    if(strlen($head_img)>64){
        $_img = $head_img;
    }
    return $_img;
}
/**
 * 显示用户头像
 */
function show_member_head_img($host='',$head_img){
    if(!$host){
        $host = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/';
    }
    if($head_img){
        if (preg_match('/(http:\/\/)|(https:\/\/)/i', $head_img))
        {
            return $head_img;
        }else{

            return $host.$head_img;
        }
    }else{
        return $host.'Public/Static/img/default_avatar.png';
    }
}


/**
 * 替换指定的部分的字符串
 * @param 被替换的内容 $arr
 * @param 替换的样式 $str
 * @param 开始位置 $star
 * @param 截取长度 $end
 */
function sub_str($arr,$str,$star,$end){
    if(empty($end)){
        $end = strlen($arr);
    }
    return  substr_replace($arr, $str, $star, $end);
}

/**
 * 获取微信操作对象
 * @param string $type  类型          
 * @return WechatReceive
 */
function &load_wechat($type = '')
{
    ! class_exists('Wechat\Loader', FALSE) && Vendor('Wechat.Loader');
    static $wechat = array();
    $index = md5(strtolower($type));
    if (! isset($wechat[$index])) {
        // 从数据库查询配置参数
        $config = M('wechat_config')->where(array(
            'type' => '1'
        ))
            ->field('token,appid,appsecret,encodingaeskey,mch_id,partnerkey,ssl_cer,ssl_key,qrc_img')
            ->find();
        // 设置SDK的缓存路径
        $config['cachepath'] = CACHE_PATH . 'Weixin/Server/';
        $wechat[$index] = & \Wechat\Loader::get_instance($type, $config);
    }
    return $wechat[$index];
}


/**
 * 检查字符串长度
 * 
 * @param [string] $str
 *            [字符串]
 * @param integer $mix
 *            [最小长度]
 * @param integer $max
 *            [最大长度]
 * @return [bool] [在判断范围内返回true 否则返回false]
 */
function check_str_len($str, $mix = 1, $max = 10)
{
    if (function_exists('mb_strlen')) {
        $len = mb_strlen($str);
    } else {
        preg_match_all("/./us", $str, $match);
        $len = count($match['0']);
    }
    return $len >= $mix && $len <= $max ? $len : false;
}

/**
 * 处理文件路径,获取基于网址的绝对路径
 * @param string $url 路径
 * @return string 是否成功
 *         @time 2016-07-4
 * @author 陆龙飞
 */
function file_url($url)
{
    if (empty($url))
        return '';
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        return $url;
    }
    return U('/', '', true, true) . $url;
}

/**
 * 生成批量插入sql
 * 
 * @param array
 *            data 二维数组
 * @param string
 *            table 表名
 * @author 陆龙飞
 *         @date 2016-01-19
 * @return string
 *
 */
function addSql($data, $table)
{
    if (empty($data) || empty($table))
        return '';
    $value = implode('`,`', array_keys($data[0]));
    $sql = 'INSERT INTO `' . C('DB_PREFIX') . $table . '` (`' . $value . '`,`add_time`) VALUES ';
    foreach ($data as $val) {
        $sql .= "(";
        foreach ($val as $k => $v) {
            is_numeric($v) ? $sql .= $v : $sql .= "'" . $v . "'";
            $sql .= ',';
        }
        $sql = rtrim($sql, ',');
        $sql .= ',"'.date('Y-m-d H:i:s',time()).'"),';
    }
    $sql = rtrim($sql, ',');
    
    return $sql;
}

/**
 * 获取微信模板消息字段
 * @time 2016-11-21
 * @param array $arr 模板数组
 * @author 陶君行<Silentlytao@outlook.com>
 */
function find_wx_template($arr)
{
    if (is_array($arr)) {
        $arr = json_encode($arr);
    }
    preg_match_all('/\{\{([\w]*)\.DATA\}\}/', $arr, $m);
    return $m['1'];
}

/** 写日志
 * @param obj $serialize 存储内容
 * @param string $item 标题
 * @time 2016-07-22
 * @author  llf  <276694999@qq.com>
 */
function write_debug($serialize,$item){
    if(!is_string($serialize)){
        $serialize = json_encode($serialize,JSON_UNESCAPED_UNICODE);
    }
    $data = array(
        'content'=>$serialize,
        'item'   =>$item,
        'addtime'=>date('Y-m-d H:i:s'),
    );
    try{
        $debug = M('debug');
        $debug->add($data);
    }catch( \Exception $e){
        write_debug(array($item,$e->getMessage()),'写日志失败');
    }
}
/**
 * 获取省市区
 * @param   $[info] [省市区信息]
 */
function get_area_str(& $info)
{
    $address = get_no_del('area');

    if($info['province_id']){
        $province = $address[$info['province_id']]['title'];
    }else{
        $info['province_id'] = $info['province'];
        $province = $address[$info['province']]['title'];
    }
    if($info['city_id']){
        $city = $address[$info['city_id']]['title'];
    }else{
        $info['city_id'] = $info['city'];
        $city = $address[$info['city']]['title'];
    }
    if($info['area_id']){
        $area = $address[$info['area_id']]['title'];
    }else{
        $info['area_id'] = $info['area'];
        $area = $address[$info['area']]['title'];
    }
    $info['province'] = $province;
    $info['city'] = $city;
    $info['area'] = $area;
    $info['all_address'] = $province . $city . $area .$info['address'];
    return $info;
}
/**
 * 程序异常操作
 * @param  [type] $data [异常操作数据]
 * @return [type]       [description]
 */
function exception_return($data)
{
    write_debug($data,'程序异常');
}
/**
 * 接口上传图片（单张|多张）
 */
function api_upload_picture($img_field,$save_path = 'Uploads/image/',$limit = 9)
{
    $config = array(
        'maxSize'    => 3145728,    
        'rootPath'   => './',
        'savePath'   => $save_path,  
        'saveName'   => array('uniqid',''),    
        'exts'       => array('jpg', 'gif', 'png', 'jpeg'),    
        'autoSub'    => true,    
        'subName'    => array('date','Ymd'),
    );
    $num = count( $_FILES[ $img_field ]['name'] );
    if($num > $limit){
        return '图片上传数量不能超过'.$limit.'张';
    }
    $upload = new \Think\Upload($config);        
    if(!is_array($_FILES[$img_field]['name'])){
        $info  = $upload->uploadOne($_FILES[$img_field]);
        unset($_FILES[$img_field]);
    }else{
        $info  = $upload->upload();
    }
    if(!$info) {
        return '图片上传失败';
    }else{
        return $info;
    }
}
/**
 * 查询订单的图片
 * @param  [type] $is_img   [是否有图片]
 * @param  [type] $order_id [订单ID]
 * @return [type]           [description]
 */
function select_order_image($is_img,$order_id)
{
    $img = array();
    if( $is_img == '1'){ 
        $img = get_result('order_image',array('order_id'=>$order_id),'','image');
        /** 图片转成全路径 */
        $img_class = new \Common\Help\ImgHelp();
        array_walk($img, function(&$a) use($img_class){ 
            $_img = $a['image'];
            $a['image'] = file_url($_img);
            $a['thumb_image'] = show_member_head_img('',$img_class->app_thumb($_img,'950','950'));
        });
    }
    return $img ? $img : array();
}
/**
 * 昵称过滤函数
 */
function filter_str($name)
{
    $name = trim($name);
    if(empty($name))  return false;
    $regex = array(
        /** 判断是否存在英文状态下的特殊字符 */
        '/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/',
        /** 判断是否存在中文状态下的特殊字符 */
        //'/[\'。，：；*？~·！@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/',
        /** 判断是否存在emoji表情 */
        //'/([0-9|#][\x{20E3}])|[\x{00ae}|\x{00a9}|\x{203C}|\x{2047}|\x{2048}|\x{2049}|\x{3030}|\x{303D}|\x{2139}|\x{2122}|\x{3297}|\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?|[\x{1F900}-\x{1F9FF}][\x{FE00}-\x{FEFF}]?/u',
    );
    foreach ($regex as $value) {
        if( preg_match($value, $name) ) return false;
    }
    $str = preg_replace_callback(
       '/./u',
       function (array $match) {
        return strlen($match[0]) >= 4 ? 'emoji_false' : $match[0];
       },
       $name);
    if( stristr($str,'false') ) return false;

    /** 判断在字符串中是否存在空格 */
    $str_arr = array();
    $str_arr = explode(' ', $name);
    if( count($str_arr ) > 1 ) return false;

    return true;
}