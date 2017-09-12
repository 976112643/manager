<?php
namespace Common\Help;

use Org\Util\String;
/**
 * 基础框架-助手类-字符串类
 */
class StrHelp extends String
{

    /**
     * 生成随机GUID码
     * 
     * @example b83ecb52-dda5-4063-b21b-a9659be79186
     * @return [string] [36位的GUID值]
     */
    public static function create_guid()
    {
        $charid = strtolower(md5(uniqid(mt_rand(), true)));
        $hyphen = chr(45); // "-"
        $uuid = substr($charid, 0, 8) . $hyphen . substr($charid, 8, 4) . $hyphen . substr($charid, 12, 4) . $hyphen . substr($charid, 16, 4) . $hyphen . substr($charid, 20, 12);
        return $uuid;
    }
    /**
     * 中英文翻译(默认英->中)
     * @param  [type] $text         [需要翻译的文本]
     * @param  string $send_lanague [发送时的语言]
     * @param  string $to_lanague   [接收时的语言]
     * @return [type]               [description]
     */
    public function translate($text,$send_lanague = 'en',$to_lanague = 'zh-CN')
    {
        $url = 'http://www.tastemylife.com/gtr.php';
        $param = array(
            'sl'=>$send_lanague,
            'tl'=>$to_lanague,
            'p'=>'1',
            'q'=>$text
        );
        $result = curl_post($url,$param);
        return json_decode($result,true);
    }
    /**
     * 字符串隐藏
     * 
     * @param $[str] [<字符串>]            
     * @param $[start] [<起始位置>]            
     * @param $[len] [<隐藏的长度>]            
     * @param $[show] [<显示的字符>]
     *            @time 2016-11-11
     * @author 陶君行<Silentlytao@outlook.com>
     */
    public static function hidden_str($str, $start = 3, $len = 4, $show = '*')
    {
        if (! is_string($str) || ! $str)
            return '';
        /**
         * 如果是邮箱，那么就隐藏@符号前面的字符串
         */
        if (filter_var($str, FILTER_VALIDATE_EMAIL)) {
            $str_arr = explode("@", $str);
            $str = $str_arr['0'];
            /**
             * 如果起始位置大于邮箱长度，那么就从第一位开始截取
             */
            $length = strlen($str);
            if ($length <= $start)
                $start = 1;
            /**
             * 如果没有设置$len,那么就默认截取到@之前一位
             */
            if ($len == 4)
                $len = $length - $start - 1;
            $change_str = str_pad($show, $len, $show, STR_PAD_BOTH);
            $str = substr_replace($str, $change_str, $start, $len);
            return $str . '@' . $str_arr['1'];
        }
        $change_str = str_pad($show, $len, $show, STR_PAD_BOTH);
        dump( $str .'-'.$change_str.'-'.$start.'-'.$len);
        dump(substr_replace($str, $change_str, $start, $len));
        return substr_replace($str, $change_str, $start, $len);
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
     * @return [bool] [在判断范围内返回字符串长度 否则返回false]
     */
    public static function check_str_len($str, $mix = 1, $max = 10)
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
     * 判断是否为空
     * @param string $str
     * @return boolean
     */
    static public function is_empty($str)
    {
        $bool = false;
        if (($str == '' || $str == NULL || empty($str)) && (! is_numeric($str)))
            $bool = true;
        return $bool;
    }

    /**
     * 判断字符串是否包含
     * @param string $str
     * @param string $ned_str 需要比较的字符串
     * @return boolean
     */
    static public function contain($str, $ned_str)
    {
        $bool = false;
        if (! self::is_empty($ned_str) && ! self::is_empty($str)) {
            $ad = strpos($str, $ned_str);
            if ($ad > 0 || ! is_bool($ad))
                $bool = true;
        }
        return $bool;
    }
    /**
     * 汉字转拼音
     * @param string $_String
     * @param string $_Code
     */
    public function Pinyin($_String = '', $_Code = 'gb2312')
    {
        $_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha" . "|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|" . "cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er" . "|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui" . "|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang" . "|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang" . "|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue" . "|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne" . "|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen" . "|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang" . "|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|" . "she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|" . "tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu" . "|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you" . "|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|" . "zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";
        $_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990" . "|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725" . "|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263" . "|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003" . "|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697" . "|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211" . "|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922" . "|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468" . "|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664" . "|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407" . "|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959" . "|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652" . "|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369" . "|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128" . "|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914" . "|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645" . "|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149" . "|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087" . "|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658" . "|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340" . "|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888" . "|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585" . "|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847" . "|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055" . "|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780" . "|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274" . "|-10270|-10262|-10260|-10256|-10254";
        $_TDataKey = explode('|', $_DataKey);
        $_TDataValue = explode('|', $_DataValue);
        $_Data = (PHP_VERSION >= '5.0') ? array_combine($_TDataKey, $_TDataValue) : $this->_Array_Combine($_TDataKey, $_TDataValue);
        arsort($_Data);
        reset($_Data);
        if ($_Code != 'gb2312')
            $_String = $this->_U2_Utf8_Gb($_String);
        $_Res = '';
        for ($i = 0; $i < strlen($_String); $i ++) {
            $_P = ord(substr($_String, $i, 1));
            if ($_P > 160) {
                $_Q = ord(substr($_String, ++ $i, 1));
                $_P = $_P * 256 + $_Q - 65536;
            }
            $_Res .= $this->_Pinyin($_P, $_Data);
        }
        return preg_replace("/[^a-z0-9]*/", '', $_Res);
    }

    /**
     * 拼音转码
     * @param string $_Num
     * @param array $_Data
     * @return string|unknown
     */
    private function _Pinyin($_Num, $_Data)
    {
        if ($_Num > 0 && $_Num < 160)
            return chr($_Num);
        elseif ($_Num < - 20319 || $_Num > - 10247)
            return '';
        else {
            foreach ($_Data as $k => $v) {
                if ($v <= $_Num)
                    break;
            }
            return $k;
        }
    }

    /**
     * 拼音转码
     * @param string $_C
     */
    private function _U2_Utf8_Gb($_C)
    {
        $_String = '';
        if ($_C < 0x80) {
            $_String .= $_C;
        } elseif ($_C < 0x800) {
            $_String .= chr(0xC0 | $_C >> 6);
            $_String .= chr(0x80 | $_C & 0x3F);
        } elseif ($_C < 0x10000) {
            $_String .= chr(0xE0 | $_C >> 12);
            $_String .= chr(0x80 | $_C >> 6 & 0x3F);
            $_String .= chr(0x80 | $_C & 0x3F);
        } elseif ($_C < 0x200000) {
            $_String .= chr(0xF0 | $_C >> 18);
            $_String .= chr(0x80 | $_C >> 12 & 0x3F);
            $_String .= chr(0x80 | $_C >> 6 & 0x3F);
            $_String .= chr(0x80 | $_C & 0x3F);
        }
        return iconv('UTF-8', 'GB2312', $_String);
    }

    /**
     * 合并数组
     * @param array $_Arr1
     * @param array $_Arr2
     */
    private function _Array_Combine($_Arr1, $_Arr2)
    {
        for ($i = 0; $i < count($_Arr1); $i ++)
            $_Res[$_Arr1[$i]] = $_Arr2[$i];
        return $_Res;
    }

    /**
     * 字符串截取函数
     * 
     * @param string $string            
     * @param number $sublen            
     * @param number $start            
     * @param string $code            
     * @return string|unknown
     */
    public function cut_str($string, $sublen, $start = 0, $code = 'UTF-8')
    {
        if ($code == 'UTF-8') {
            $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
            preg_match_all($pa, $string, $t_string);
            
            if (count($t_string[0]) - $start > $sublen)
                return join('', array_slice($t_string[0], $start, $sublen)) . "...";
            return join('', array_slice($t_string[0], $start, $sublen));
        } else {
            $start = $start * 2;
            $sublen = $sublen * 2;
            $strlen = strlen($string);
            $tmpstr = '';
            
            for ($i = 0; $i < $strlen; $i ++) {
                if ($i >= $start && $i < ($start + $sublen)) {
                    if (ord(substr($string, $i, 1)) > 129) {
                        $tmpstr .= substr($string, $i, 2);
                    } else {
                        $tmpstr .= substr($string, $i, 1);
                    }
                }
                if (ord(substr($string, $i, 1)) > 129)
                    $i ++;
            }
            if (strlen($tmpstr) < $strlen)
                $tmpstr .= "...";
            return $tmpstr;
        }
    }

    /**
     * 把数字1-1亿换成汉字表述，如：123->一百二十三
     * 
     * @param [num] $num
     *            [数字]
     * @return [string] [string]
     */
    function numToWord($num)
    {
        $chiNum = array(
            '零',
            '一',
            '二',
            '三',
            '四',
            '五',
            '六',
            '七',
            '八',
            '九'
        );
        $chiUni = array(
            '',
            '十',
            '百',
            '千',
            '万',
            '亿',
            '十',
            '百',
            '千'
        );
        
        $chiStr = '';
        
        $num_str = (string) $num;
        
        $count = strlen($num_str);
        $last_flag = true; // 上一个 是否为0
        $zero_flag = true; // 是否第一个
        $temp_num = null; // 临时数字
        
        $chiStr = ''; // 拼接结果
        if ($count == 2) { // 两位数
            $temp_num = $num_str[0];
            $chiStr = $temp_num == 1 ? $chiUni[1] : $chiNum[$temp_num] . $chiUni[1];
            $temp_num = $num_str[1];
            $chiStr .= $temp_num == 0 ? '' : $chiNum[$temp_num];
        } else 
            if ($count > 2) {
                $index = 0;
                for ($i = $count - 1; $i >= 0; $i --) {
                    $temp_num = $num_str[$i];
                    if ($temp_num == 0) {
                        if (! $zero_flag && ! $last_flag) {
                            $chiStr = $chiNum[$temp_num] . $chiStr;
                            $last_flag = true;
                        }
                    } else {
                        $chiStr = $chiNum[$temp_num] . $chiUni[$index % 9] . $chiStr;
                        
                        $zero_flag = false;
                        $last_flag = false;
                    }
                    $index ++;
                }
            } else {
                $chiStr = $chiNum[$num_str[0]];
            }
        return $chiStr;
    }

    /**
     * 数字转换为中文
     * 
     * @param string|integer|float $num
     *            目标数字
     * @param integer $mode
     *            模式[true:金额（默认）,false:普通数字表示]
     * @param boolean $sim
     *            使用小写（默认）
     * @return string
     */
    function numberTochinese($num, $mode = true, $sim = true)
    {
        if (! is_numeric($num))
            return '含有非数字非小数点字符！';
        $char = $sim ? array(
            '零',
            '一',
            '二',
            '三',
            '四',
            '五',
            '六',
            '七',
            '八',
            '九'
        ) : array(
            '零',
            '壹',
            '贰',
            '叁',
            '肆',
            '伍',
            '陆',
            '柒',
            '捌',
            '玖'
        );
        $unit = $sim ? array(
            '',
            '十',
            '百',
            '千',
            '',
            '万',
            '亿',
            '兆'
        ) : array(
            '',
            '拾',
            '佰',
            '仟',
            '',
            '萬',
            '億',
            '兆'
        );
        $retval = $mode ? '元' : '点';
        // 小数部分
        if (strpos($num, '.')) {
            list ($num, $dec) = explode('.', $num);
            $dec = strval(round($dec, 2));
            if ($mode) {
                $retval .= "{$char[$dec['0']]}角{$char[$dec['1']]}分";
            } else {
                for ($i = 0, $c = strlen($dec); $i < $c; $i ++) {
                    $retval .= $char[$dec[$i]];
                }
            }
        }
        // 整数部分
        $str = $mode ? strrev(intval($num)) : strrev($num);
        for ($i = 0, $c = strlen($str); $i < $c; $i ++) {
            $out[$i] = $char[$str[$i]];
            if ($mode) {
                $out[$i] .= $str[$i] != '0' ? $unit[$i % 4] : '';
                if ($i > 1 and $str[$i] + $str[$i - 1] == 0) {
                    $out[$i] = '';
                }
                if ($i % 4 == 0) {
                    $out[$i] .= $unit[4 + floor($i / 4)];
                }
            }
        }
        $retval = join('', array_reverse($out)) . $retval;
        return $retval;
    }
}
?>