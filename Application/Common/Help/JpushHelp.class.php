<?php

namespace Common\Help;
/**
 * 极光推送，定时消息类
 */
class JpushHelp extends BaseHelp
{
    /** 定时任务类*/
    protected $class;
    /** 推送消息类类*/
    protected $push_class;
    /**
     * 构造函数
     */
    public function __autoload()
    {
        // $config = C('THINK_JIGUANG');
        // $app_key= $config['app_key'];
        // $master_secret=$config['master_secret'];
        $config = C('THINK_JIGUANG');
        $app_key = 'ae25e7a97d46b352a0bff8f8';
        $master_secret = '1fcb3d4291866d759a4ca98e';
        $client = new \JPush\Client($app_key, $master_secret);
        $this->class = $client->schedule();
        $this->push_class = $client->push();
    }
    /**
     * 测试MODEL
     */
    public function test()
    {
        // var_dump(json_decode('{"headers":{"http_code":"HTTP\/1.1 400 BAD REQUEST","Server":"nginx","Date":"Thu, 24 Nov 2016 07:10:33 GMT","Content-Type":"application\/json","Transfer-Encoding":"chunked","Connection":"keep-alive"},"body":"{\"error\":{\"message\":\"The schedule-task is invalid, push is invalid:cannot find user by this audience\",\"code\":8100}}","http_code":400',true));
        // die();
        // die(date('Y-m-d H:i:s'));
        $time = trim(I('time'));
        $info = array(
            'name' => 'name_test',
            'time' => $time,
            'id' => 'all',
            'text' => '测试',
            'content' => array(
                'url' => 'http://api.ai-t.com.cn/Uploads/Voice/Test/2016-11-24/583690a36d8be.wav'
            ),
            'sound' => 'http://api.ai-t.com.cn/Uploads/Voice/Test/2016-11-24/583690a36d8be.wav',
            'type' => 'Sinlang',
            'title' => 'ceshi'
        );
        $res = $this->single($info);
        if ($res) {
            $res['time'] = $time;
            $res['server_now_time'] = date('Y-m-d H:i:s');
            $a = $res;
            $this->apiReturn(array(
                'status' => '1',
                'msg' => 'ok',
                'info' => $res
            ));
        } else {
            $this->apiReturn();
        }
    }

    /**
     * 创建定时任务
     * {
     * "name": "Schedule_Name", //定时任务的名称
     * "enabled": true, // 创建定时任务时 为 true
     * "trigger": {
     * "single":{
     * "time": "2014-09-17 12:00:00" //YYYY-MM-DD HH:MM:SS time为必选项且格式为"YYYY-mm-dd HH:MM:SS“，如"2014-02-15 13:16:59"，不能为"2014-2-15 13:16:59"或"2014-12-15 13:16"，即日期时间格式必须完整
     * }
     * },
     * "push": {
     * "platform": "all",
     * "audience": "all",
     * "notification": {
     * "alert" : "Hello, JPush!"
     * },
     * "message": {
     * "msg_content":"Message!"
     * },
     * "options": {
     * "time_to_live":60
     * }
     * }
     * }
     * @time 2016-11-21
     * @param  array $info 
     * @author 陶君行<Silentlytao@outlook.com>
     */
    public function single($info)
    {
        $name = $info['name'] ? $info['name'] : 'Schedule_Name_' . NOW_TIME;
        $push_payload = $this->create_push_data($info);
        $trigger = array(
            'time' => $info['time']
        );
        $res = $this->class->createSingleSchedule($name, $push_payload, $trigger);
        if ($res) {
            return $res['body'];
        } else {
            return false;
        }
    }

    /**
     * 推送定期任务
     * {
     * "name": "Schedule_Name",
     * "enabled": true,
     * "trigger": {
     * "periodical": {
     * "start":"2014-09-17 12:00:00", 表示定期任务有效起始时间，格式与必须严格为：'YYYY-mm-dd HH:MM:SS‘，且时间为24小时制。
     * "end": "2014-09-18 12:00:00", 表示定期任务的过期时间，格式同上
     * "time": "12:00:00", 表示触发定期任务的定期执行时间,，格式严格为：'HH:MM:SS’，且为24小时制
     * "time_unit": "WEEK", //month, week, day, 大小写不敏感 表示定期任务的执行的最小时间单位有：day、week及month3种。大小写不敏感，如day, Day,DAy均为合法的time_unit
     * "frequency": 1, 此项与time_unit的乘积共同表示的定期任务的执行周期，如time_unit为day，frequency为2，则表示每两天触发一次推送，目前支持的最大值为100。
     * "point": ["WED","FRI"] //time_unit为day时候，point不能存在。WED,FRI 大小写不敏感。month:"01","02"
     * }
     * },
     * "push": {
     * "platform": "all",
     * "audience": "all",
     * "notification": {"alert" : "Hello, JPush!" },
     * "message": {"msg_content":"Message!" },
     * "options": {"time_to_live":60}
     * }
     * }
     * @param array $info
     */
    public function periodical($info)
    {
        $name = $info['name'] ? $info['name'] : 'Schedule_Name_' . NOW_TIME;
        $push_payload = $this->create_push_data($info);
        $trigger = array(
            'start' => $info['start'],
            'end' => $info['end'],
            'time' => $info['time'],
            'time_unit' => $info['time_unit'],
            'frequency' => $info['frequency'],
            'point' => $info['point']
        );
        /**
         * 如果time_unit为day时候,删除$trigger['point']
         */
        if (strcasecmp($info['time_unit'], 'day') == 0) {
            unset($trigger['point']);
        }
        $res = $this->class->createPeriodicalSchedule($name, $push_payload, $trigger);
        if ($res) {
            return $res['body'];
        } else {
            return false;
        }
    }

    /**
     * 修改定时任务
     * @param array $info 需要发送的内容数组
     */
    public function update_single($info)
    {
        $name = $info['name'] ? $info['name'] : null;
        if ($info['update']) {
            $push_payload = $this->create_push_data($info['update']);
        }
        if ($info['time']) {
            $trigger = array(
                'time' => $info['time']
            );
        }
        if ($info['enable']) {
            $enable = true;
        }
        $res = $this->class->updateSingleSchedule($info['schedule_id'], $name, $enable, $push_payload, $trigger);
        if ($res) {
            return $res['body'];
        } else {
            return false;
        }
    }

    /**
     * 修改定期任务
     * @param array $info 需要发送的内容数组
     */
    public function update_periodical($info)
    {
        $name = $info['name'] ? $info['name'] : null;
        if ($info['update']) {
            $push_payload = $this->create_push_data($info['update']);
        }
        if ($info['time']) {
            $trigger = array(
                'start' => $info['start'],
                'end' => $info['end'],
                'time' => $info['time'],
                'time_unit' => $info['time_unit'],
                'frequency' => $info['frequency'],
                'point' => $info['point']
            );
        }
        if ($info['enable']) {
            $enable = true;
        }
        /**
         * 如果time_unit为day时候,删除$trigger['point']
         */
        if (strcasecmp($info['time_unit'], 'day') == 0) {
            unset($trigger['point']);
        }
        $res = $this->class->updatePeriodicalSchedule($info['schedule_id'], $name, $enable, $push_payload, $trigger);
        if ($res) {
            return $res['body'];
        } else {
            return false;
        }
    }

    /**
     * 获取定时任务列表
     * @param string $page 需分页标志
     */
    public function get_list($page = '1')
    {
        $res = $this->class->getSchedules($page);
        if ($res) {
            return $res['body'];
        } else {
            return false;
        }
    }

    /**
     * 获取指定定时任务
     * @param string $schedule_id 定时任务ID
     */
    public function get_one($schedule_id)
    {
        $res = $this->class->getSchedule($schedule_id);
        if ($res) {
            return $res['body'];
        } else {
            return false;
        }
    }

    /**
     * 删除定时任务
     * @param string $schedule_id 定时任务ID
     */
    public function del_one($schedule_id)
    {
        $res = $this->class->deleteSchedule($schedule_id);
        if ($res) {
            return $res['body'];
        } else {
            return false;
        }
    }

    /**
     * ******************************************私有方法 -S **************************************
     */
    /**
     * 创建Push参数(同公共方法的tuisong($Info))类似
     * @param array $info 需要发送的内容数组
     */
    private function create_push_data($info)
    {
        /**
         * 设置平台，ios,android
         */
        $pusher = $this->push_class;
        $pusher->setPlatform([
            'ios',
            'android'
        ]);
        $info['id'] = strval($info['id']);
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
        $pusher->iosNotification($info['text'], array( // IOS个性化通知
            'sound' => $info['sound'], // 通知提示声音 sound.caf 符合ios的声音通知文件
            'content-available' => true, // 推送唤醒
            'category' => 'naoke',
            'extras' => $info['content']
        ))
            ->androidNotification($info['text'], array( // android个性化通知
            'title' => $info['title'], // 通知标题
            'build_id' => 2, // 通知栏样式
            'extras' => $info['content']
        ) // 扩展字段JSON格式字符串
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
        return $pusher->build();
    }
/**
 * ******************************************私有方法 -E **************************************
 */
}

?>