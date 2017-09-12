<?php
namespace Common\Help;
/**
 * 基础框架-基础算法实现
 */
class AlgoHelo extends BaseHelp
{

    /**
     * ——————————————————————————————————————————————————————
     * 基础数据结构算法
     * ——————————————————————————————————————————————————————
     */
    
    /**
     * 二分查找
     * 
     * @param array $array            
     * @param number $low            
     * @param number $high            
     * @param number $k            
     * @return string
     */
    function bin_sch($array, $low, $high, $k)
    {
        if ($low <= $high) {
            $mid = intval(($low + $high) / 2);
            if ($array[$mid] == $k) {
                return $mid;
            } elseif ($k < $array[$mid]) {
                return bin_sch($array, $low, $mid - 1, $k);
            } else {
                return bin_sch($array, $mid + 1, $high, $k);
            }
        }
        return - 1;
    }

    /**
     * 顺序查找（数组里查找某个元素）
     * 
     * @param unknown $array            
     * @param unknown $n            
     * @param unknown $k            
     * @return number
     */
    function seq_sch($array, $n, $k)
    {
        $array[$n] = $k;
        for ($i = 0; $i < $n; $i ++) {
            if ($array[$i] == $k) {
                break;
            }
        }
        if ($i < $n) {
            return $i;
        } else {
            return - 1;
        }
    }

    /**
     * 线性表的删除（数组中实现）
     * 
     * @param unknown $array            
     * @param unknown $i            
     * @return unknown
     */
    function delete_array_element($array, $i)
    {
        $len = count($array);
        for ($j = $i; $j < $len; $j ++) {
            $array[$j] = $array[$j + 1];
        }
        array_pop($array);
        return $array;
    }

    /**
     * 冒泡排序（数组排序）
     * 
     * @param unknown $array            
     * @return boolean|unknown
     */
    function bubble_sort($array)
    {
        $count = count($array);
        if ($count <= 0)
            return false;
        for ($i = 0; $i < $count; $i ++) {
            for ($j = $count - 1; $j > $i; $j --) {
                if ($array[$j] < $array[$j - 1]) {
                    $tmp = $array[$j];
                    $array[$j] = $array[$j - 1];
                    $array[$j - 1] = $tmp;
                }
            }
        }
        return $array;
    }

    /**
     * 快速排序（数组排序）
     * 
     * @param unknown $array            
     * @return unknown
     */
    function quick_sort($array)
    {
        if (count($array) <= 1)
            return $array;
        $key = $array[0];
        $left_arr = array();
        $right_arr = array();
        for ($i = 1; $i < count($array); $i ++) {
            if ($array[$i] <= $key)
                $left_arr[] = $array[$i];
            else
                $right_arr[] = $array[$i];
        }
        $left_arr = quick_sort($left_arr);
        $right_arr = quick_sort($right_arr);
        return array_merge($left_arr, array(
            $key
        ), $right_arr);
    }

    /**
     * 1、冒泡法
     * 思路分析：在要排序的一组数中，对当前还未排好的序列，
     * 从前往后对相邻的两个数依次进行比较和调整，让较大的数往下沉，较小的往上冒。
     * 即，每当两相邻的数比较后发现它们的排序与排序要求相反时，就将它们互换。
     * 比如：
     * 第一次循环:第一步(1:43)第二步(43:54)第三步(54:62)第四步(62:21)这时则死换变成了(21:62)........(76:39)
     * 第二次循环:第一步(1:43)第二步(43:54)第三步(54:62)第四步(62:21)这时则死换变成了(21:62)........(36:76)
     * @param array $arr
     */
    function bubbleSort(array $arr)
    {
        $len = count($arr);
        // 该层循环控制 需要冒泡的轮数
        for ($i = 1; $i < $len; $i ++) { // 该层循环用来控制每轮 冒出一个数 需要比较的次数
            for ($k = 0; $k < $len - $i; $k ++) {
                if ($arr[$k] > $arr[$k + 1]) {
                    $tmp = $arr[$k + 1];
                    $arr[$k + 1] = $arr[$k];
                    $arr[$k] = $tmp;
                }
            }
        }
        return $arr;
    }

    /**
     * 2.选择排序
     * 思路分析：在要排序的一组数中，选出最小的一个数与第一个位置的数交换。
     * 然后在剩下的数当中再找最小的与第二个位置的数交换，如此循环到倒数第二个数和最后一个数比较为止。
     * @param array $arr
     */
    function selectSort(array $arr)
    {
        // 双重循环完成，外层控制轮数，内层控制比较次数(7.5.2.9.3)
        $len = count($arr);
        for ($i = 0; $i < $len - 1; $i ++) {
            // 先假设最小的值的位置
            $p = $i;
            
            for ($j = $i + 1; $j < $len; $j ++) {
                // $arr[$p] 是当前已知的最小值
                if ($arr[$p] > $arr[$j]) {
                    // 比较，发现更小的,记录下最小值的位置；并且在下次比较时采用已知的最小值进行比较。
                    $p = $j;
                }
            }
            // 已经确定了当前的最小值的位置，保存到$p中。如果发现最小值的位置与当前假设的位置$i不同，则位置互换即可。
            if ($p != $i) {
                $tmp = $arr[$p];
                $arr[$p] = $arr[$i];
                $arr[$i] = $tmp;
            }
        }
        // 返回最终结果
        return $arr;
    }

    /**
     * 需要将处于第二维键名为name，其值相同的数组的value合并,形成一个新的数组。
     * 比如上面代码中的name为fileds_510的两个二维数组，就应该合并为一个值为足球,棒球的数组
     *
     * array(8) {
     * [0]=>
     * array(2) {
     * ["name"]=>
     * string(4) "name"
     * ["value"]=>
     * string(6) "青叶"
     * }
     * [1]=>
     * array(2) {
     * ["name"]=>
     * string(5) "phone"
     * ["value"]=>
     * string(11) "13812341234"
     * }
     * [2]=>
     * array(2) {
     * ["name"]=>
     * string(12) "fileds_507[]"
     * ["value"]=>
     * string(12) "我是青叶"
     * }
     * [3]=>
     * array(2) {
     * ["name"]=>
     * string(12) "fileds_508[]"
     * ["value"]=>
     * string(6) "合肥"
     * }
     * [4]=>
     * array(2) {
     * ["name"]=>
     * string(12) "fileds_509[]"
     * ["value"]=>
     * string(3) "男"
     * }
     * [5]=>
     * array(2) {
     * ["name"]=>
     * string(12) "fileds_510[]"
     * ["value"]=>
     * string(6) "足球"
     * }
     * [6]=>
     * array(2) {
     * ["name"]=>
     * string(12) "fileds_510[]"
     * ["value"]=>
     * string(6) "棒球"
     * }
     * [7]=>
     * array(2) {
     * ["name"]=>
     * string(12) "fileds_511[]"
     * ["value"]=>
     * string(16) "2016-12-15T11:15"
     * }
     * }
     * @param array $arr
     */
    function sort_array(array $arr)
    {
        $public_info = $arr;
        for ($i = 0; $i < count($public_info); $i ++) {
            for ($j = $i + 1; $j < count($public_info); $j ++) {
                if ($public_info[$j]['name'] == $public_info[$i]['name']) {
                    $public_info[$i]['value'] .= ',' . $public_info[$j]['value'];
                    unset($public_info[$j]);
                }
            }
        }
        return $public_info;
    }

    /**
     * ——————————————————————————————————————————————————————
     * PHP内置字符串函数实现
     * ——————————————————————————————————————————————————————
     */
    
    /**
     * 字符串长度
     * 
     * @param unknown $str            
     * @return number
     */
    function strlen($str)
    {
        if ($str == '')
            return 0;
        $count = 0;
        while (1) {
            if ($str[$count] != NULL) {
                $count ++;
                continue;
            } else {
                break;
            }
        }
        return $count;
    }

    /**
     * 截取子串
     * 
     * @param unknown $str            
     * @param unknown $start            
     * @param unknown $length            
     * @return void|unknown
     */
    function substr($str, $start, $length = NULL)
    {
        if ($str == '' || $start > strlen($str))
            return;
        if (($length != NULL) && ($start > 0) && ($length > strlen($str) - $start))
            return;
        if (($length != NULL) && ($start < 0) && ($length > strlen($str) + $start))
            return;
        if ($length == NULL)
            $length = (strlen($str) - $start);
        
        if ($start < 0) {
            for ($i = (strlen($str) + $start); $i < (strlen($str) + $start + $length); $i ++) {
                $substr .= $str[$i];
            }
        }
        if ($length > 0) {
            for ($i = $start; $i < ($start + $length); $i ++) {
                $substr .= $str[$i];
            }
        }
        if ($length < 0) {
            for ($i = $start; $i < (strlen($str) + $length); $i ++) {
                $substr .= $str[$i];
            }
        }
        return $substr;
    }

    /**
     * 字符串翻转
     * 
     * @param unknown $str            
     * @return number|unknown
     */
    function strrev($str)
    {
        if ($str == '')
            return 0;
        for ($i = (strlen($str) - 1); $i >= 0; $i --) {
            $rev_str .= $str[$i];
        }
        return $rev_str;
    }

    /**
     * 字符串比较
     * 
     * @param unknown $s1            
     * @param unknown $s2            
     * @return number|boolean
     */
    function strcmp($s1, $s2)
    {
        if (strlen($s1) < strlen($s2))
            return - 1;
        if (strlen($s1) > strlen($s2))
            return 1;
        for ($i = 0; $i < strlen($s1); $i ++) {
            if ($s1[$i] == $s2[$i]) {
                continue;
            } else {
                return false;
            }
        }
        return 0;
    }

    /**
     * 查找字符串
     * 
     * @param unknown $str            
     * @param unknown $substr            
     * @return boolean|number
     */
    function strstr($str, $substr)
    {
        $m = strlen($str);
        $n = strlen($substr);
        if ($m < $n)
            return false;
        for ($i = 0; $i <= ($m - $n + 1); $i ++) {
            $sub = substr($str, $i, $n);
            if (strcmp($sub, $substr) == 0)
                return $i;
        }
        return false;
    }

    /**
     * 字符串替换
     * 
     * @param unknown $substr            
     * @param unknown $newsubstr            
     * @param unknown $str            
     * @return boolean|unknown
     */
    function str_replace($substr, $newsubstr, $str)
    {
        $m = strlen($str);
        $n = strlen($substr);
        $x = strlen($newsubstr);
        if (strchr($str, $substr) == false)
            return false;
        for ($i = 0; $i <= ($m - $n + 1); $i ++) {
            $i = strchr($str, $substr);
            $str = str_delete($str, $i, $n);
            $str = str_insert($str, $i, $newstr);
        }
        return $str;
    }

    /**
     * ——————————————————————————————————————————————————————
     * 自实现字符串处理函数
     * ——————————————————————————————————————————————————————
     */
    
    /**
     * 插入一段字符串
     * 
     * @param unknown $str            
     * @param unknown $i            
     * @param unknown $substr            
     * @return string
     */
    function str_insert($str, $i, $substr)
    {
        for ($j = 0; $j < $i; $j ++) {
            $startstr .= $str[$j];
        }
        for ($j = $i; $j < strlen($str); $j ++) {
            $laststr .= $str[$j];
        }
        $str = ($startstr . $substr . $laststr);
        return $str;
    }

    /**
     * 删除一段字符串
     * 
     * @param unknown $str            
     * @param unknown $i            
     * @param unknown $j            
     * @return string
     */
    function str_delete($str, $i, $j)
    {
        for ($c = 0; $c < $i; $c ++) {
            $startstr .= $str[$c];
        }
        for ($c = ($i + $j); $c < strlen($str); $c ++) {
            $laststr .= $str[$c];
        }
        $str = ($startstr . $laststr);
        return $str;
    }

    /**
     * 复制字符串
     * 
     * @param unknown $s1            
     * @param unknown $s2            
     * @return void|unknown
     */
    function strcpy($s1, $s2)
    {
        if (strlen($s1) == NULL || ! isset($s2))
            return;
        for ($i = 0; $i < strlen($s1); $i ++) {
            $s2[] = $s1[$i];
        }
        return $s2;
    }

    /**
     * 连接字符串
     * 
     * @param unknown $s1            
     * @param unknown $s2            
     * @return void|unknown
     */
    function strcat($s1, $s2)
    {
        if (! isset($s1) || ! isset($s2))
            return;
        $newstr = $s1;
        for ($i = 0; $i < count($s); $i ++) {
            $newstr .= $st[$i];
        }
        return $newsstr;
    }

    /**
     * 简单编码函数（与php_decode函数对应）
     * 
     * @param unknown $str            
     * @return boolean|string
     */
    function php_encode($str)
    {
        if ($str == '' && strlen($str) > 128)
            return false;
        for ($i = 0; $i < strlen($str); $i ++) {
            $c = ord($str[$i]);
            if ($c > 31 && $c < 107)
                $c += 20;
            if ($c > 106 && $c < 127)
                $c -= 75;
            $word = chr($c);
            $s .= $word;
        }
        return $s;
    }

    /**
     * 简单解码函数（与php_encode函数对应）
     * 
     * @param unknown $str            
     * @return boolean|string
     */
    function php_decode($str)
    {
        if ($str == '' && strlen($str) > 128)
            return false;
        for ($i = 0; $i < strlen($str); $i ++) {
            $c = ord($word);
            if ($c > 106 && $c < 127)
                $c = $c - 20;
            if ($c > 31 && $c < 107)
                $c = $c + 75;
            $word = chr($c);
            $s .= $word;
        }
        return $s;
    }

    /**
     * 简单加密函数（与php_decrypt函数对应）
     * 
     * @param unknown $str            
     * @return boolean|string
     */
    function php_encrypt($str)
    {
        $encrypt_key = 'abcdefghijklmnopqrstuvwxyz1234567890';
        $decrypt_key = 'ngzqtcobmuhelkpdawxfyivrsj2468021359';
        if (strlen($str) == 0)
            return false;
        for ($i = 0; $i < strlen($str); $i ++) {
            for ($j = 0; $j < strlen($encrypt_key); $j ++) {
                if ($str[$i] == $encrypt_key[$j]) {
                    $enstr .= $decrypt_key[$j];
                    break;
                }
            }
        }
        return $enstr;
    }

    /**
     * 简单解密函数（与php_encrypt函数对应）
     * 
     * @param unknown $str            
     * @return boolean|string
     */
    function php_decrypt($str)
    {
        $encrypt_key = 'abcdefghijklmnopqrstuvwxyz1234567890';
        $decrypt_key = 'ngzqtcobmuhelkpdawxfyivrsj2468021359';
        if (strlen($str) == 0)
            return false;
        for ($i = 0; $i < strlen($str); $i ++) {
            for ($j = 0; $j < strlen($decrypt_key); $j ++) {
                if ($str[$i] == $decrypt_key[$j]) {
                    $enstr .= $encrypt_key[$j];
                    break;
                }
            }
        }
        return $enstr;
    }
}

?>