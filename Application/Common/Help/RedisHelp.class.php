<?php
namespace Common\Help;

use Think\Cache;
/**
 * Redis缓存驱动
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 * @category   Think
 * @package  Cache
 * @subpackage  Driver
 * @author huangzengbing
 */
class RedisHelp extends Cache
{

    /**
     * 类对象实例数组
     * 共有静态变量
     * 
     * @param mixed $_instance存放实例            
     */
    private static $_instance = array();

    /**
     * 每次实例的句柄
     * 保护变量
     */
    protected $handler;

    /**
     * redis的配置
     * 全局 静态变量
     * 静态的方法里调用静态变量和静态方法只能用self，不能出现$this
     */
    static $option = array();

    /**
     * 架构函数，必须设置为私有，防止外部new
     * 实例化redis驱动的实例，寄一个socket
     * @param string $host   访问URL
     * @param string $port   访问端口号
     * @param string $auth   是否加密
     */
    private function __construct($host, $port, $auth)
    {
        if (! $this->handler) {
            $this->handler = new \Redis();
        }
        $func = self::$option['persistent'] ? 'pconnect' : 'connect';
        if (self::$option['timeout'] === false) {
            $this->handler->$func($host, $port);
        } else {
            $this->handler->$func($host, $port, self::$option['timeout']);
        }
        
        //dump($auth);die;
        // 认证
        try{
            if ($auth) {
                $this->handler->auth($auth);
            }
        }catch( \Exception $e){
            throw new \Exception('请开启Redis');
        }

    }

    /**
     * 实例函数，单例入口
     * 共有，静态函数
     * @param array $options 实例化配置
     * @return resource
     */
    public static function getInstance($type = '', $options = array())
    {
        // 判断是否存在redis扩展
        if (! extension_loaded('redis')) {
            E(L('_NOT_SUPPERT_') . ':redis');
        }
        if (empty($options)) {
            $options = array(
                'host' => C('DATA_REDIS_HOST') ? C('DATA_REDIS_HOST') : '127.0.0.1',
                'port' => C('DATA_REDIS_PORT') ? C('DATA_REDIS_PORT') : 6379,
                'timeout' => C('DATA_CACHE_TIME') ? C('DATA_CACHE_TIME') : false,
                'persistent' => C('DATA_PERSISTENT') ? C('DATA_PERSISTENT') : false,
                'auth' => C('DATA_REDIS_AUTH') ? C('DATA_REDIS_AUTH') : false
            );
        }
        $options['host'] = explode(',', $options['host']);
        $options['port'] = explode(',', $options['port']);
        $options['auth'] = explode(',', $options['auth']);
        foreach ($options['host'] as $key => $value) {
            if (! isset($options['port'][$key])) {
                $options['port'][$key] = $options['port'][0];
            }
            if (! isset($options['auth'][$key])) {
                $options['auth'][$key] = $options['auth'][0];
            }
        }
        self::$option = $options;
        self::$option['expire'] = isset($options['expire']) ? $options['expire'] : C('DATA_EXPIRE');
        self::$option['prefix'] = isset($options['prefix']) ? $options['prefix'] : C('DATA_CACHE_PREFIX');
        self::$option['length'] = isset($options['length']) ? $options['length'] : 0;
        // 一次性创建redis的在不同host的实例
        foreach (self::$option['host'] as $i => $server) {
            $host = self::$option['host'][$i];
            $port = self::$option['port'][$i];
            $auth = self::$option['auth'][$i];
            if (! (self::$_instance[$i] instanceof self)) {
                self::$_instance[$i] = new self($host, intval($port), $auth);
            }
        }
        // 默认返回第一个实例，即master
        return reset(self::$_instance);
    }

    /**
     * 判断是否master/slave,调用不同的master或者slave实例
     * @param boolean $master 是否主从配置
     */
    public function is_master($master = true)
    {
        if ($master) {
            $i = 0;
        } else {
            $count = count(self::$option['host']);
            if ($count == 1) {
                $i = 0;
            } else {
                $i = rand(1, $count - 1);
            }
        }
        // 返回每一个实例的句柄
        return self::$_instance[$i]->handler;
    }

    /**
     * ********************************************字符串操作*****************************************************
     */
    /**
     * 读取缓存，随机从slave服务器中读缓存
     * 
     * @access public
     * @param string $name
     *            缓存变量名
     * @param boolean $flag 是否开启主从配置
     * @return mixed
     */
    public function get($name, $flag = true)
    {
        $redis = $this->is_master(false);
        $value = $redis->get(self::$option['prefix'] . $name);
        if ($flag) {
            $jsonData = json_decode($value, true);
            // 检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
            return ($jsonData === NULL) ? $value : $jsonData;
        } else {
            return $value;
        }
    }

    /**
     * 写入缓存，写入master的redis服务器
     * 
     * @access public
     * @param string $name
     *            缓存变量名
     * @param mixed $value
     *            存储数据
     * @param integer $expire
     *            有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = null)
    {
        $redis = $this->is_master(true);
        N('cache_write', 1);
        if (is_null($expire)) {
            $expire = self::$option['expire'];
        }
        $name = self::$option['prefix'] . $name;
        // 对数组/对象数据进行缓存处理，保证数据完整性
        $value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if (is_int($expire) && $expire > 0) {
            $result = $redis->setex($name, $expire, $value);
        } else {
            $result = $redis->set($name, $value);
        }
        if ($result && self::$option['length'] > 0) {
            // 记录缓存队列
            $this->queue($name);
        }
        return $result;
    }
    /**
     * Redis EXISTS 命令用于检查给定 key 是否存在
     *
     * @access public
     * @param  [string] $key [缓存变量名]
     * @return boolean    若 key 存在返回 1 ，否则返回 0 
     */
    public function exists($key)
    {
        $redis = $this->is_master(true);
        return $redis->EXISTS($key);
    }
    /**
     * ****************************************************list操作******************************************
     */
    /**
     * 将一个或多个值插入到列表头部
     * 
     * @access public
     * @param string $name
     *            缓存变量名
     * @param mixed $value
     *            存储数据
     * @param integer $expire
     *            有效时间（秒）
     * @return boolean
     */
    public function list_lpush($name, $value, $expire = null)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        // 对数组/对象数据进行缓存处理，保证数据完整性
        $value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        $result = $redis->lpush($name, $value);
        if (is_int($expire) && $expire > 0) {
            $redis->expire($name, $expire);
        }
        
        return $result;
    }

    /**
     * 将一个或多个值插入到已存在的列表头部
     * 
     * @access public
     * @param string $name
     *            缓存变量名
     * @param mixed $value
     *            存储数据
     * @param integer $expire
     *            有效时间（秒）
     * @return boolean
     */
    public function list_lpushx($name, $value, $expire = null)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        // 对数组/对象数据进行缓存处理，保证数据完整性
        $value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        $result = $redis->lpushx($name, $value);
        if (is_int($expire) && $expire > 0) {
            $redis->expire($name, $expire);
        }
        return $result;
    }

    /**
     * 返回名称为key的list中start至end之间的元素
     * 其中 0 表示列表的第一个元素，
     * 1 表示列表的第二个元素，以此类推。
     * 你也可以使用负数下标，以 -1 表示列表的最后一个元素，
     * -2 表示列表的倒数第二个元素，以此类推。
     * @param unknown $name
     * @param unknown $begin
     * @param unknown $end
     * @return unknown
     */
    public function list_lrange($name, $begin, $end)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        
        $result = $redis->lrange($name, $begin, $end);
        
        return $result;
    }

    /**
     * 删除队列中左起(右起使用-1)1个字符’_'(若有)
     * @param unknown $name
     * @param unknown $value
     * @return unknown
     */
    public function list_lrem($name, $value)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        // return $redis->lrem($name,$count,$value);
        $result = $redis->lrem($name, $value);
        return $result;
    }

    /**
     * 获取数据
     * @param unknown $name
     * @param number $begin
     * @param unknown $end
     * @return unknown
     */
    public function get_list($name, $begin = 0, $end = -1)
    {
        $redis = $this->is_master(true);
        $value = $redis->lrange(self::$option['prefix'] . $name, $begin, $end);
        return $value;
    }

    /**
     * 将一个或多个值插入到列表尾部
     * 
     * @access public
     * @param string $name
     *            缓存变量名
     * @param mixed $value
     *            存储数据
     * @param integer $expire
     *            有效时间（秒）
     * @return boolean
     */
    public function list_rpush($name, $value, $expire = null)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        // 对数组/对象数据进行缓存处理，保证数据完整性
        $value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        $result = $redis->rpush($name, $value);
        if (is_int($expire) && $expire > 0) {
            $redis->expire($name, $expire);
        }
        
        return $result;
    }

    /**
     * 将一个或多个值插入到已存在的列表尾部
     * 
     * @access public
     * @param string $name
     *            缓存变量名
     * @param mixed $value
     *            存储数据
     * @param integer $expire
     *            有效时间（秒）
     * @return boolean
     */
    public function list_rpushx($name, $value, $expire = null)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        // 对数组/对象数据进行缓存处理，保证数据完整性
        $value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        $result = $redis->rpushx($name, $value);
        if (is_int($expire) && $expire > 0) {
            $redis->expire($name, $expire);
        }
        
        return $result;
    }

    /**
     * 返回当前列表长度
     * 
     * @access public
     * @param string $name
     *            缓存变量名
     * @return boolean
     */
    public function list_llen($name)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        return $redis->llen($name);
    }

    /**
     * 返回指定顺序位置的list元素
     * 
     * @access public
     * @param string $name
     *            缓存变量名
     * @param string $count
     *            顺序值
     * @return string
     */
    public function list_lindex($name, $count = '1')
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        return $redis->lindex($name, $count);
    }

    /**
     * 修改队列中指定位置的value
     * 
     * @access public
     * @param string $name
     *            缓存变量名
     * @param string $count
     *            顺序值
     * @param string $value
     *            变量值
     * @return string
     */
    public function list_lset($name, $count, $value)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        $value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        return $redis->lset($name, $count, $value);
    }

    /**
     * lpop 类似栈结构地弹出(并删除)最左的一个元素
     * 
     * @access public
     * @param string $name
     *            缓存变量名
     * @return string
     */
    public function list_lpop($name)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        return $redis->lpop($name);
    }

    /**
     * rpop 类似栈结构地弹出(并删除)最右的一个元素
     * 
     * @access public
     * @param string $name
     *            缓存变量名
     * @return string
     */
    public function list_rpop($name)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        return $redis->rpop($name);
    }

    /**
     * ltrim 队列修改，保留左边起若干元素，其余删除
     * 
     * @access public
     * @param string $name
     *            缓存变量名
     * @param string $begin
     *            起始值
     * @param string $end
     *            结束值
     * @return string
     */
    public function list_ltrim($name, $begin, $end)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        return $redis->ltrim($name, $begin, $end);
    }

    /**
     * rpoplpush 从一个队列中pop出元素并push到另一个队列
     * 也适用于同一个队列,把最后一个元素移到头部list2 =>array(‘ab3′,’ab1′,’ab2′)
     * 
     * @param string $name            
     * @param string $new_name            
     * @return list
     */
    public function list_rpoplpush($name, $new_name)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        $new_name = self::$option['prefix'] . $new_name;
        return $redis->rpoplpush($name, $new_name);
    }

    /**
     * linsert 在队列的中间指定元素前或后插入元素
     * 
     * @example $redis->linsert(‘list2′, ‘before’,'ab1′,’123′); //表示在元素’ab1′之前插入’123′
     *          $redis->linsert(‘list2′, ‘after’,'ab1′,’456′); //表示在元素’ab1′之后插入’456′
     * @param string $name            
     * @param string $inset_seat            
     * @param string $seat_name            
     * @param string $value            
     * @return boolean;
     */
    public function list_linsert($name, $inset_seat, $seat_name, $value)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        $value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        return $redis->linsert($name, $inset_seat, $seat_name, $value);
    }

    /**
     * ****************************************************set(集合)操作******************************************
     */
    
    /**
     * Redis的Set是string类型的无序集合。集合成员是唯一的，这就意味着集合中不能出现重复的数据。
     * Redis 中 集合是通过哈希表实现的，所以添加，删除，查找的复杂度都是O(1)。
     * return sadd 增加元素,返回true,重复返回false
     * @param unknown $name
     * @param unknown $value
     * @param unknown $expire
     */
    public function set_sadd($name, $value, $expire = null)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        // 对数组/对象数据进行缓存处理，保证数据完整性
        $value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        
        $result = $redis->sadd($name, $value);
        if (is_int($expire) && $expire > 0) {
            $redis->expire($name, $expire);
        }
        return $result;
    }

    /**
     * 批量插入set 集合
     * @param unknown $arr
     * @return unknown
     */
    public function set_sadd_more($arr)
    {
        $redis = $this->is_master(true);
        foreach ($arr as $k => $v) {
            foreach ($v as $value) {
                $result = $redis->sadd($k, $value);
            }
        }
        return $result;
    }

    /**
     * 移除指定元素
     * @param unknown $name
     * @param unknown $value
     * @return unknown
     */
    public function set_srem($name, $value)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        $result = $redis->srem($name, $value);
        return $result;
    }

    /**
     * 移动当前set表的指定元素到另一个set表
     * 移动’name′中的’value’到’name1′,返回true or false
     * @param unknown $name
     * @param unknown $name1
     * @param unknown $value
     * @return unknown
     */
    public function set_smove($name, $name1, $value)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        $name1 = self::$option['prefix'] . $name1;
        $result = $redis->smove($name, $name1, $value);
        return $result;
    }

    /**
     * 返回当前set表元素个数
     * @param unknown $name
     * @return unknown
     */
    public function set_scard($name)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        $result = $redis->scard($name);
        return $result;
    }

    /**
     * 判断元素是否属于当前表
     * @param unknown $name
     * @param unknown $value
     * @return unknown
     */
    public function set_sismember($name, $value)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        $result = $redis->sismember($name, $value);
        return $result;
    }

    /**
     * 返回当前表的所有元素
     * @param unknown $name
     * @return unknown
     */
    public function set_smembers($name)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        $result = $redis->smembers($name);
        return $result;
    }

    /**
     * sinter/sunion/sdiff 返回两个表中元素的交集/并集/补集
     * $type 1: 交集 2:并集 3:补集
     * @param unknown $name
     * @param unknown $name1
     * @param unknown $type
     * @return unknown
     */
    public function set_jihe($name, $name1, $type)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        $name1 = self::$option['prefix'] . $name1;
        switch ($type) {
            case 1:
                $result = $redis->sinter($name, $name1);
                break;
            case 2:
                $result = $redis->sunion($name, $name1);
                break;
            case 3:
                $result = $redis->sdiff($name, $name1);
                break;
            default:
                ;
        }
        
        return $result;
    }

    /**
     * sinter/sunion/sdiff 返回三个表中元素的交集/并集/补集
     * $type 1: 交集 2:并集 3:补集
     * return array
     * @param unknown $name
     * @param unknown $name1
     * @param unknown $name2
     * @param unknown $type
     * @return unknown
     */
    public function set_jihe_3($name, $name1, $name2, $type)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        $name1 = self::$option['prefix'] . $name1;
        $name2 = self::$option['prefix'] . $name2;
        switch ($type) {
            case 1:
                $result = $redis->sinter($name, $name1, $name2);
                break;
            case 2:
                $result = $redis->sunion($name, $name1, $name2);
                break;
            case 3:
                $result = $redis->sdiff($name, $name1, $name2);
                break;
            default:
                ;
        }
        
        return $result;
    }

    /**
     * 返回表中一个随机元素
     * @param unknown $name
     * @return unknown
     */
    public function set_srandmember($name)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        $result = $redis->srandmember($name);
        return $result;
    }

    /**
     * ****************************************************set(有序)操作******************************************
     */
    /**
     * 增加元素,
     * 并设置序号,返回true,重复返回false
     * 当key存在但不是有序集类型时，返回一个错误。
     * @param unknown $name
     * @param unknown $sort
     * @param unknown $value
     * @param unknown $expire
     * @return unknown
     */
    public function set_zadd($name, $sort, $value, $expire = null)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        $result = $redis->zadd($name, $sort, $value);
        if (is_int($expire) && $expire > 0) {
            $redis->expire($name, $expire);
        }
        return $result;
    }

    /**
     * 成员的分数值，以字符串形式表示。
     * 当key存在但不是有序集类型时，返回一个错误。
     * @param unknown $name
     * @param unknown $value
     * @return unknown|number
     */
    public function get_zscore($name, $value)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        $result = $redis->EXISTS($name);
        if ($result) {
            $value = $redis->zscore($name, $value);
            return $value;
        } else {
            $result = $redis->zadd($name, 0, $value);
            return 0;
        }
    }

    /**
     * 命令对有序集合中指定成员的分数加上增量 increment。
     * 当 key 不存在，或分数不是 key 的成员时， ZINCRBY key increment member 等同于 ZADD key increment member 。
     *
     * 当 key 不是有序集类型时，返回一个错误。
     *
     * 分数值可以是整数值或双精度浮点数。
     * @param unknown $name
     * @param unknown $increment
     * @param unknown $member
     */
    public function zadd_zincrby($name, $increment, $member)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        return $redis->zincrby($name, $increment, $member);
    }
    /**
     * 移除指定元素,
     * 并设置序号,返回true,重复返回false
     * 当key存在但不是有序集类型时，返回一个错误。
     * @param unknown $name
     * @param unknown $value
     * @return unknown
     */
    public function set_zrem($name, $value)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        $result = $redis->zrem($name, $value);
        return $result;
    }
    /**
     * 对指定元素索引值的增减,改变元素排列次序
     * @param unknown $name
     * @param unknown $num
     * @param unknown $value
     * @return unknown
     */
    public function set_zincrby($name, $num, $value)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        $result = $redis->zincrby($name, 1, $value);
        return $result;
    }

    /**
     * 设置多个值到redis
     * @param unknown $array_mset
     */
    public function set_mset($array_mset)
    {
        $redis = $this->is_master(true);
        
        $redis->mset($array_mset);
    }
    /**
     * Redis Zrange 返回有序集中，指定区间内的成员
     * 其中成员的位置按分数值递增(从小到大)来排序
     * @param  [type] $name  [键]
     * @param  [type] $start [开始]
     * @param  [type] $end   [结束]
     * @return [type]        [description]
     */
    public function get_zrange($name,$start = '0',$end = '-1')
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        return $redis->zrange($name,$start,$end,WITHSCORES);
    }
    /**
     * Redis Zrange 返回有序集中，指定区间内的成员
     * 其中成员的位置按分数值递增(从小到大)来排序
     * @param  [type] $name  [键]
     * @param  [type] $start [开始]
     * @param  [type] $end   [结束]
     * @return [type]        [description]
     */
    public function get_zrange_no_pre($name,$start = '0',$end = '-1')
    {
        $redis = $this->is_master(true);

        return $redis->zrange($name,$start,$end,WITHSCORES);
    }
    /**
     * Redis Zrevrange  返回有序集中，指定区间内的成员
     * 其中成员的位置按分数值递增(从大到小)来排序
     * @param  [type] $name  [键]
     * @param  [type] $start [开始]
     * @param  [type] $end   [结束]
     * @return [type]        [description]
     */
    public function get_zrevrange_no_pre($name,$start = '0',$end = '-1')
    {
        $redis = $this->is_master(true);

        return $redis->zrevrange($name,$start,$end,WITHSCORES);
    }
    /**
     * Redis Zcount 命令用于计算有序集合中指定分数区间的成员数量
     * 分数值在 min 和 max 之间的成员的数量
     * @param  [type] $name [description]
     * @param  [type] $min  [description]
     * @param  [type] $max  [description]
     * @return [type]       [description]
     */
    public function get_zcount($name,$min,$max)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        return $redis->zcount($name,$min,$max);
    }
    /**
     * 命令用于迭代有序集合中的元素（包括元素成员和元素分值）
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function get_zscan($name)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        return $redis->zscan($name);
    }
    /**
     * ****************************************************hash操作******************************************
     */
    /**
     * 插入多个值
     * @param unknown $key
     * @param unknown $data
     */
    public function hmset($key, $data)
    {
        $redis = $this->is_master(true);
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    $data[$k] = json_encode($v);
                }
            }
        }
        $result = $redis->hmset(self::$option['prefix'] .$key, $data);
        return $result;
    }

    /**
     * 获取hash表的值
     * @param unknown $name
     * @param unknown $value
     * @param unknown $expire
     * @return unknown
     */
    public function hmget($name, $value, $expire = null)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        if (! $value) {
            $result = $redis->hgetall($name);
        } else {
            $result = $redis->hmget($name, $value);
        }
        
        return $result;
    }

    /**
     * Hset 命令用于为哈希表中的字段赋值
     * @param unknown $key
     * @param unknown $name
     * @param unknown $value
     */
    public function hset($key, $name, $value)
    {
        $redis = $this->is_master(true);
        $key = self::$option['prefix'] . $key;
        return $redis->hset($key, $name, $value);
    }

    /**
     * hkeys 获取所有哈希表中的字段
     * @param unknown $key
     */
    public function hkeys($key)
    {
        $redis = $this->is_master(true);
        $key = self::$option['prefix'] . $key;
        return $redis->hkeys($key);
    }

    /**
     * Hvals 命令 - 获取哈希表中所有值
     * @param unknown $key
     */
    public function hvals($key)
    {
        $redis = $this->is_master(true);
        $key = self::$option['prefix'] . $key;
        return $redis->hvals($key);
    }

    /**
     * Hlen 命令 - 获取哈希表中字段的数量
     * @param unknown $key
     */
    public function hlen($key)
    {
        $redis = $this->is_master(true);
        $key = self::$option['prefix'] . $key;
        return $redis->hlen($key);
    }

    /**
     * Hdel 命令 - 删除一个或多个哈希表字段
     * @param unknown $key
     * @param unknown $field
     */
    public function hdel($key, $field)
    {
        $redis = $this->is_master(true);
        $key = self::$option['prefix'] . $key;
        return $redis->hdel($key, $field);
    }
    /**
     * Redis Hsetnx 命令用于为哈希表中不存在的的字段赋值 。
     * 如果哈希表不存在，一个新的哈希表被创建并进行 HSET 操作。
     * 如果字段已经存在于哈希表中，操作无效。
     * 如果 key 不存在，一个新哈希表被创建并执行 HSETNX 命令
     * @param  [type] $key   [description]
     * @param  [type] $name  [description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function hsetnx($key,$name,$value)
    {
        $redis = $this->is_master(true);
        $key = self::$option['prefix'] . $key;
        return $redis->hsetnx($key,$name,$value);
    }
    /**
     * Redis Hexists 命令用于查看哈希表的指定字段是否存在。
     * @param  [type] $key  [description]
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function hexists($key,$name)
    {
        $redis = $this->is_master(true);
        $key = self::$option['prefix'] . $key;
        return $redis->hexists($key,$name);
    }

    /**
     *  查看键值的过期时间
     * 如果只是改名，是不会改变之前的过期时间的
     * @param unknown $name
     */
    public function ttl($name)
    {
        $redis = $this->is_master(true);
        return $redis->ttl(self::$option['prefix'] . $name);
    }

    
    /**
     * 删除多个key 
     * @param unknown $name
     */
    public function del_more($name)
    {
        $redis = $this->is_master(true);
        $list = $redis->keys(self::$option['prefix'] . $name . '*');
        return $redis->del($list);
    }

    /**
     * 删除单个key
     * @param unknown $name
     */
    public function del($name)
    {
        $redis = $this->is_master(true);
        $list = $redis->keys(self::$option['prefix'] . $name);
        return $redis->del($list);
    }

    /**
     * 删除缓存
     * 
     * @access public
     * @param string $name
     *            缓存变量名
     * @return boolean
     */
    public function rm($name)
    {
        $redis = $this->is_master(true);
        return $redis->delete(self::$option['prefix'] . $name);
    }

    /**
     * 清除缓存
     * 
     * @access public
     * @return boolean
     */
    public function clear()
    {
        $redis = $this->is_master(true);
        return $redis->flushDB();
    }

    /**
     * move 移动当库的元素到其它库
     * @param unknown $name
     * @param unknown $db
     * @return unknown
     */
    public function move($name, $db)
    {
        $redis = $this->is_master(true);
        $name = self::$option['prefix'] . $name;
        $result = $redis->move($name, $db); // 若’mydb2′库存在
        return $result;
    }

    /**
     * 查询匹配键
     * @param  [type]  $name   [键值]
     * @param  boolean $is_pre [是否启用前缀匹配]
     * @return [type]          [description]
     */
    public function search($name,$is_pre = true)
    {
        $redis = $this->is_master(true);

        if( $is_pre ){
            $name = self::$option['prefix'] . $name . '*';
        }
        $result = $redis->keys($name); // 若’mydb2′库存在
        return $result;
    }

    /**
     * 当前服务器的状态 
     * @param unknown $name
     * @return unknown
     */
    public function rinfo($name)
    {
        $redis = $this->is_master(true);
        
        $result = $redis->info(); // 若’mydb2′库存在
        return $result;
    }

    /**
     * 禁止外部克隆对象
     */
    private function __clone()
    {}
    
    // 可以根据需要，继续添加phpredis的驱动api.
    
    /**
     * 关闭长连接
     * 
     * @access public
     */
    public function __destruct()
    {
        if (self::$option['persistent'] == 'pconnect') {
            // 关闭master的长连接，不可以写，但slave任然可以读
            $redis = $this->is_master(true);
            $redis->close();
        }
    }
}
?>