<?php
namespace Common\Help;
/**
 * 基础框架-助手类-推送消息类
 */
class PushHelp extends BaseHelp
{

    /**
     * 发送友盟推送消息
     * 
     * @param integer $uid
     *            用户id
     * @param string $title
     *            推送的标题
     * @return boolear 是否成功
     */
    public function Umeng_push($info)
    {
        // 导入友盟
        Vendor('Umeng.Umeng');
        $umeng_config = C('THINK_SDK_UMENG');
        // 自定义字段 根据实际环境分配；如果不用可以忽略
        $status = 1;
        // 消息未读总数统计 根据实际环境获取未读的消息总数 此数量会显示在app图标右上角
        $count_number = 1;
        $data = array(
            'key' => 'status',
            'value' => "$status",
            'count_number' => $count_number
        );
        /**
         * IOS推送
         */
        $key1 = $umeng_config['UMENG_IOS_APP_KEY'];
        $secret1 = $umeng_config['UMENG_IOS_SECRET'];
        $umeng1 = new \Umeng($key1, $secret1);
        $result1 = $umeng1->sendIOSCustomizedcast($data, $info);
        /**
         * android推送
         */
        $key2 = $umeng_config['UMENG_ANDROID_APP_KEY'];
        $secret2 = $umeng_config['UMENG_ANDROID_SECRET'];
        $umeng2 = new \Umeng($key2, $secret2);
        $result2 = $umeng2->sendAndroidCustomizedcast($data, $info);
        if ($result1 !== true) {
            $flag['IOS'] = $result1;
        }
        if ($result2 !== true) {
            $flag['Android'] = $result2;
        }
        if (isset($flag)) {
            // 后续可以自行记录到日志中分析
            return $flag;
        } else {
            return true;
        }
    }

    /**
     * 极光推送消息
     * 
     * @param array $info            
     * @example $info = array(
     *          'id'=>'32', //[人员ID 或者'all']
     *          'title'=>'标题',
     *          'text' =>'描述',
     *          'content' =>'内容', //[推送通知内容]
     *          'type'=>'sina' //和客户端协议的类型
     *         
     *          );
     *         
     */
    public function Jg_push($info)
    {
        /**
         * 在Core/Library/Jpush里面
         */
        if ($info['id'] == "") {
            $info['status'] = 0;
            $info['info'] = "消息发送失败，没有发送对象";
            return $info;
        }
        if ($info['title'] == "") {
            $info['status'] = 0;
            $info['info'] = "消息发送失败，标题为空";
            return $info;
        }
        $config = C('THINK_JIGUANG');
        $app_key = $config['app_key'];
        $master_secret = $config['master_secret'];
        $client = new \JPush\Client($app_key, $master_secret);
        $pusher = $client->push();
        /**
         * 设置平台，ios,android
         */
        $pusher->setPlatform(array('android','ios'));
        //$pusher->setPlatform('ios');
        if (! is_array($info['id'])) {
            $info['id'] = strval($info['id']);
        }
        if ($info['id'] == "all") {
            /**
             * 设置接受人员
             */
            $pusher->addAllAudience();
        } else {
            $pusher->addAlias($info['id']);
        }
        /**
         * 设置推送消息内容
         */
        $pusher->setNotificationAlert($info['text']);
        /**
         * 设置推送消息具体内容
         */
         $pusher->iosNotification($info['title'], array( //IOS个性化通知
         'sound' => 'hello jpush', //通知提示声音
         'content-available' => true, //推送唤醒
         'category' => 'naoke',
         'extras' => $info['content'],
         ))
            ->androidNotification($info['text'], array( // android个性化通知
            'title' => $info['title'], // 通知标题
            'builder_id' => 2, // 通知栏样式
            'extras' => $info['content']
        ) // 扩展字段数组格式
)
            ->message($info['text'], array( // 推送消息
            'title' => $info['title'],
            'content_type' => $info['type'], // 消息内容类型
            'extras' => $info['content']
        ))
            ->options(array( // 可选参数
            'time_to_live' => 86400, // 离线消息时间1一天，不允许超过10天
            'apns_production' => false
        ) // true 生产环境 false 开发环境
);
        try {
            $pusher->send();
        } catch (\JPush\Exceptions\JPushException $e) {
            $info['status'] = 0;
            $info['info'] = $e;
            return $info;
        }
    }

    /**
     * 微信推送模板消息
     * 获取数据库中模板对应的消息体格式推送消息
     * 
     * @example $info = array(
     *          'tpl_id' =>'4',
     *          'openid'=>'oJZJ-xHZIoBTBfFjp0zyhgYHbse0',
     *          'url' =>'http://test.cnsunrun.com'
     *          'data'=>array('9999','天上人间',date('Y-m-d'),'remark')
     *          );
     * @param array $info 发送的内容数组
     */
    public function Weixin_push($info)
    {
        $tpl_info = get_info('wx_temp', array(
            'id' => $info['tpl_id']
        ));
        if ($tpl_info) {
            if (is_array($tpl_info)) {
                $arr = json_encode($tpl_info);
            }
            preg_match_all('/\{\{([\w]*)\.DATA\}\}/', $arr, $m);
            $filed_list = $m['1'];
            /**
             * 这里，只需要$Info传递一个$info['data'] =array('标题','内容','文字');
             * $filed_list的个数应该和$Info['data']的个数一致，否则需要查看数据
             * 循环给$data 数组赋值的形式去生成一个微信需要的格式
             */
            $temp = array();
            foreach ($filed_list as $k => $v) {
                $a[$v]['value'] = $info['data'][$k];
                $a[$v]['color'] = '#173177';
                $temp = $a;
            }
            $data = array(
                'touser' => $info['openid'],
                'template_id' => $tpl_info['template_id'],
                'url' => $info['url'],
                'data' => $temp
            );
            $class = & load_wechat('Temp');
            $res = $class->send($data);
            /**
             * 后续可以自行插入日志
             */
            if ($res) {
                return true;
            } else {
                write_debug(array(
                    'errorMsg' => $class->get_error(),
                    'data' => $data
                ), '推送微信消息失败');
                return $class->get_error();
            }
        } else {
            return '不存在的模板';
        }
    }
}
?>