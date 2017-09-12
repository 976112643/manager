<?php
namespace Common\Help;

use Org\Util\Date;
/**
 * 基础框架-助手类-日期类
 */
class DateHelp extends Date
{

    /**
     * 查询本周的开始时间和结束时间
     */
    public function getWeek()
    {
        $date = new \DateTime();
        $date->modify('this week');
        $start = $date->format('Y-m-d');
        $date->modify('this week +6 days');
        $end = $date->format('Y-m-d');
        
        return array(
            'start' => $start,
            'end' => $end
        );
    }

    /**
     * 查询本月的开始时间和结束时间
     */
    public function getMonth()
    {
        $date = date('Y-m-d');
        $start = date('Y-m-01', strtotime($date));
        $end = date('Y-m-d', strtotime("$start +1 month -1 day"));
        return array(
            'start' => $start,
            'end' => $end
        );
    }

    /**
     * 查询本年的开始时间和结束时间
     */
    public function getYear()
    {
        $start = date('Y', time()) . '-1' . '-01';
        $end = date('Y', time()) . '-12' . '-31';
        return array(
            'start' => $start,
            'end' => $end
        );
    }

    /**
     * 获取一周的所有日期
     * @param string $week 当前日期
     * @param string $format 日期格式
     */
    public function getWeekList($week = '', $format = 'Y-m-d')
    {
        // 当前日期
        $sdefaultDate = $week ? $week : date("Y-m-d");
        // $first =1 表示每周星期一为开始日期 0表示每周日为开始日期
        $first = 1;
        // 获取当前周的第几天 周日是 0 周一到周六是 1 - 6
        $w = date('w', strtotime($sdefaultDate));
        // 获取本周开始日期，如果$w是0，则表示周日，减去 6 天
        $start = strtotime("$sdefaultDate -" . ($w ? $w - $first : 6) . ' days');
        $end = $start + 24 * 3600 * 6;
        $days = array();
        for ($i = $start; $i <= $end; $i += 24 * 3600)
            $days[] = date($format, $i);
        return $days;
    }

    /**
     * 获取一个月的日期
     * @param string $month 当前月份
     * @param string $format 日期格式
     */
    public function getMonthList($month = "this month", $format = "Y-m-d")
    {
        $start = strtotime("first day of $month");
        $end = strtotime("last day of $month");
        $days = array();
        for ($i = $start; $i <= $end; $i += 24 * 3600)
            $days[] = date($format, $i);
        return $days;
    }

    /**
     * 获取一年的日期
     * @param string $year 今年
     * @param string $format 日期格式
     */
    public function getYearList($year = '', $format = 'Y-m-d')
    {
        $year = $year ? $year : date('Y', time());
        $start = strtotime($year . '-1' . '-01');
        $end = strtotime($year . '-12' . '-31');
        $days = array();
        for ($i = $start; $i <= $end; $i += 24 * 3600)
            $days[] = date($format, $i);
        return $days;
    }

    /**
     * 时间戳转换 刚刚、几分钟、几小时、昨天、前天、
     * @param int $time            
     * @return string
     */
    public function transfer_time($time)
    {
        $rtime = date("Y-m-d", $time);
        $htime = date("H:i", $time);
        
        $time = time() - $time;
        switch ($time) {
            case $time < 60:
                $str = '刚刚';
                break;
            case $time < 3600:
                $min = floor($time / 60);
                $str = $min . '分钟前';
                break;
            case $time < 3600 * 24:
                $str = '今天' . $htime;
                break;
            case $time < 3600 * 24 * 3:
                $d = floor($time / (60 * 60 * 24));
                if ($d == 1) {
                    $str = '昨天 ' . $htime;
                } else {
                    $str = '前天 ' . $htime;
                }
                break;
            default:
                $str = $rtime;
        }
        return $str;
    }
    /**
     * 根据生日计算年龄
     */
    public function calc_age($birthday)
    {
        $age = 0;
        if(!empty($birthday)){
            $age = strtotime($birthday);
            if($age === false){
                return 0;
            }

            list($y1,$m1,$d1) = explode("-",date("Y-m-d", $age));

            list($y2,$m2,$d2) = explode("-",date("Y-m-d"), time());

            $age = $y2 - $y1;
            if((int)($m2.$d2) < (int)($m1.$d1)){
                $age -= 1;
            }
        }
        return $age;
    }
    /** 
    *根据出生生日取得星座 
    * 
    *@param String $brithday 用于得到星座的日期 格式为yyyy-mm-dd 
    * 
    *@param Array $format 用于返回星座的名称 
    * 
    *@return String 
    */ 
    public function get_constellation($birthday, $format=null) { 
        
        $time = strtotime($birthday);
        list($year,$month,$day) = explode('-', date("Y-m-d", $time) );

        if ($month <1 || $month>12 || $day < 1 || $day >31) 
        { 
            return null; 
        } 
        //设定星座数组 
        $constellations = array( 
              '摩羯座', '水瓶座', '双鱼座', '白羊座', '金牛座', '双子座', '巨蟹座','狮子座', '处女座', '天秤座', '天蝎座', '射手座'
              ); 

        //设定星座结束日期的数组，用于判断 
        $enddays = array(19, 18, 20, 20, 20, 21, 22, 22, 22, 22, 21, 21,); 
        //如果参数format被设置，则返回值采用format提供的数组，否则使用默认的数组 
        if ($format != null) 
        { 
            $values = $format; 
        } 
        else 
        { 
            $values = $constellations; 
        } 
        //根据月份和日期判断星座 
        switch ($month) 
        { 
            case 1: 
              if ($day <= $enddays['0']) 
              { 
                $constellation = $values['0']; 
              } 
              else 
              { 
                $constellation = $values['1']; 
              } 
              break; 
            case 2: 
              if ($day <= $enddays['1']) 
              { 
                $constellation = $values['1']; 
              } 
              else 
              { 
                $constellation = $values['2']; 
              } 
              break; 
            case 3: 
              if ($day <= $enddays['2']) 
              { 
                $constellation = $values['2']; 
              } 
              else 
              { 
                $constellation = $values['3']; 
              } 
              break; 
            case 4: 
              if ($day <= $enddays['3']) 
              { 
                $constellation = $values['3']; 
              } 
              else 
              { 
                $constellation = $values['4']; 
              } 
              break; 
            case 5: 
              if ($day <= $enddays['4']) 
              { 
                $constellation = $values['4']; 
              } 
              else 
              { 
                $constellation = $values['5']; 
              } 
              break; 
            case 6: 
              if ($day <= $enddays['5']) 
              { 
                $constellation = $values['5']; 
              } 
              else 
              { 
                $constellation = $values['6']; 
              } 
              break; 
            case 7: 
              if ($day <= $enddays['6']) 
              { 
                $constellation = $values['6']; 
              } 
              else 
              { 
                $constellation = $values['7']; 
              } 
              break; 
            case 8: 
              if ($day <= $enddays['7']) 
              { 
                $constellation = $values['7']; 
              } 
              else 
              { 
                $constellation = $values['8']; 
              } 
              break; 
            case 9: 
              if ($day <= $enddays['8']) 
              { 
                $constellation = $values['8']; 
              } 
              else 
              { 
                $constellation = $values['9']; 
              } 
              break; 
            case 10: 
              if ($day <= $enddays['9']) 
              { 
                $constellation = $values['9']; 
              } 
              else 
              { 
                $constellation = $values['10']; 
              } 
              break; 
            case 11: 
              if ($day <= $enddays['10']) 
              { 
                $constellation = $values['10']; 
              } 
              else 
              { 
                $constellation = $values['11']; 
              } 
              break; 
            case 12: 
              if ($day <= $enddays['11']) 
              { 
                $constellation = $values['11']; 
              } 
              else 
              { 
                $constellation = $values['0']; 
              } 
              break; 
        } 
    
        return $constellation; 
    } 
    /**
    * 时间间距
    * @param $type 1 return string 天时
    *              2 return string 天时分
    *              3 return string 天时分秒
    * @author Jozh liu
    */
    public function last_time($big, $small, $type=1){
       if ( strlen($big) != 10 || !is_int($big) ) return false;
       if ( strlen($small) != 10 || !is_int($small) ) return false;
       if ($big < $small) return '已超出截止时间';

       $return = $re = abs($big-$small);

       $return = '';
       if ($d = floor($re/3600/24)) $return .= $d.'天';
       if ($h = floor(($re-3600*24*$d)/3600)) $return .= $h.'小时';
       if ( $type == 2 ) {
           $i = floor(($re-3600*24*$d-3600*$h)/60);
           $return .= $i.'分';
       }
       if ( $type == 3 ) {
           $i = floor(($re-3600*24*$d-3600*$h)/60);
           $return .= $i.'分';
           $s = floor($re-3600*24*$d-3600*$h-60*$i);
           $return .= $s.'秒';
       }

       return '还剩'.$return;
    }
    /**
     * 顺手APP时间计算
     * 如果时间超出一小时但是不超过24个小时，就用当前几小时之前，如果时间超出24个小时，按照昨天。超出48小时，按照2天前展示 以此类推
     */
    public function time_diff( $date )
    {
        $time = strtotime($date);
        $now_time = time();

        $date=floor(($now_time-$time)/86400);
        $hour=floor(($now_time-$time)%86400/3600);
        $minute=floor(($now_time-$time)%86400/60);
        $second=floor(($now_time-$time)%86400%60);

        $diff_time = $now_time - $time;
        switch ($diff_time) {
            case $diff_time < 60:
                $str = '刚刚';
                break;
            case $diff_time < 3600:
                $str = $minute . '分钟前';
                break;
            case $diff_time < 3600 * 24:
                $str = $hour .'小时前';
                break;
            case $diff_time < 3600 * 24 * 3:
                $d = floor($time / (60 * 60 * 24));
                if ($d == 1) {
                    $str = '昨天 ' . date('H:i',$time);
                }else{
                    $str = $date .'天前';
                }
                break;
            default:

                $str = $date .'天前';
                break;
        }
        return $str;
    }
}
?>