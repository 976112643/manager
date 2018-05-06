<?php
/**
 * 前后台配置
 * @package
 */

return array(
	/* 多语言配置 */
	'LANG_SWITCH_ON' => true,
    'LANG_AUTO_DETECT' => true, // 自动侦测语言 开启多语言功能后有效
    'DEFAULT_LANG' => 'zh-cn', // 默认语言
    'LANG_LIST' => 'zh-cn,en-us', // 允许切换的语言列表 用逗号分隔
    'VAR_LANGUAGE' => 'l', // 默认语言切换变量
    /* 日志设置 */
    'LOG_RECORD' => true, // 默认不记录日志
    'LOG_LEVEL' => 'EMERG,ALERT,CRIT,ERR,WARN,NOTIC,INFO,DEBUG,SQL', // 允许记录的日志级别
    'LOG_EXCEPTION_RECORD' => true, // 是否记录异常信息日志
    /* 扩展函数引入 */
    'LOAD_EXT_FILE' => 'image,extend',
	/* 模板相关配置 */
	'TMPL_PARSE_STRING' => array(
        '__STATIC__' => __ROOT__ . '/Public/Static',
        '__IMG__' => __ROOT__ . '/Public/Home/img',
        '__CSS__' => __ROOT__ . '/Public/Home/css',
        '__JS__' => __ROOT__ . '/Public/Home/js',
        '__PLUGIN__' => __ROOT__ . '/Public/Plugins',
        '__SELLER__' => __ROOT__ . '/Public/Seller',
        '__USER__' => __ROOT__ . '/Public/User'
    ),
    
    // 加载扩展配置文件
    'LOAD_EXT_CONFIG' => 'db',
    'SESSION_PREFIX' => 'sc_',
    // '配置项'=>'配置值'
    
    'SHOW_PAGE_TRACE' => true,
	
	/* 数据缓存设置 */
	'DATA_CACHE_PREFIX' => 'sr_', // 缓存前缀
    'DATA_CACHE_TYPE' => 'File', // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator
    
    'CONTROLLER_LEVEL' => 2, // 设置1级目录的控制器层
    'MODULE_ALLOW_LIST' => array(
        'Api',
        'Home',
		'Backend',
		'Test'
    ),
    
    'DEFAULT_MODULE' => 'Home',
    'DEFAULT_CONTROLLER' => 'Index/Index',
	/* URL配置 */
	'URL_CASE_INSENSITIVE' => true, // 默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL' => 2, // URL模式
    'VAR_URL_PARAMS' => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR' => '/', // PATHINFO URL分割符
                                
    'TMPL_ACTION_ERROR' => 'Application/Home/View/Status/error.html', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS' => 'Application/Home/View/Status/success.html', // 默认成功跳转对应的模板文件
    //'TMPL_EXCEPTION_FILE' => 'Application/Home/View/Status/exception.html',// 异常页面的模板文件                                                             // 'TMPL_EXCEPTION_FILE' => 'Application/Home/View/Status/exception.html',// 异常页面的模板文件
    
    /* 图片上传相关配置 */
    'IMG_UPLOAD' => array(
        'mimes' => '', // 允许上传的文件MiMe类型
        'maxSize' => 20 * 1024 * 1024, // 上传的文件大小限制 (0-不做限制)
        'exts' => 'jpg,gif,png,jpeg,bmp', // 允许上传的文件后缀
        'autoSub' => true, // 自动子目录保存文件
        'subName' => '', // 子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Runtime/Uploads/ImgTemp/', // 保存根路径
        'savePath' => '', // 保存路径
        'saveName' => array(
            'uniqid',
            ''
        ), // 上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt' => '', // 文件保存后缀，空则使用原后缀
        'replace' => false, // 存在同名是否覆盖
        'hash' => true, // 是否生成hash编码
        'callback' => false
    ), // 检测文件是否存在回调函数，如果存在返回文件信息数组
    
    /* 文件上传相关配置 */
    'FILE_UPLOAD' => array(
        'mimes' => '', // 允许上传的文件MiMe类型
        'exts' => 'jpg,jpeg,bmp,png,rar,zip,7z,doc,docx,rtf,txt,xls,xlsx,ppt,pptx,pdf,apk', // 允许上传的文件后缀
        'autoSub' => true, // 自动子目录保存文件
        'subName' => '', // 子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Runtime/Uploads/FileTemp/', // 保存根路径
        'savePath' => '', // 保存路径
        'saveName' => array(
            'uniqid',
            ''
        ), // 上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt' => '', // 文件保存后缀，空则使用原后缀
        'replace' => false, // 存在同名是否覆盖
        'hash' => true, // 是否生成hash编码
        'callback' => false
    ), // 检测文件是否存在回调函数，如果存在返回文件信息数组
    
    /* 文件上传相关配置 */
    'FILE_UPLOAD_APK' => array(
        'mimes' => '', // 允许上传的文件MiMe类型
        'exts' => 'apk', // 允许上传的文件后缀
        'autoSub' => true, // 自动子目录保存文件
        'subName' => '', // 子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Runtime/Uploads/FileTemp/', // 保存根路径
        'savePath' => '', // 保存路径
        'saveName' => array(
            'uniqid',
            ''
        ), // 上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt' => '', // 文件保存后缀，空则使用原后缀
        'replace' => false, // 存在同名是否覆盖
        'hash' => true, // 是否生成hash编码
        'callback' => false
    ), // 检测文件是否存在回调函数，如果存在返回文件信息数组
    
    /* 支付接口参数配置 */
    'payment' => array(
		/*支付宝配置*/
		'alipay' => array(
            'email' => 'longhefenghuang@126.com',				/* 收款账号邮箱*/
			'key' => '9b4tao4km6ktg2e805bwpti6y55oew3f',	 /*加密key，开通支付宝账户后给予*/
			'partner' => '2088221726008512',				/* 合作者ID，支付宝有该配置，开通易宝账户后给予*/
            'app_id'=>'2017061607501207',
			'ali_public_key_path' => CONF_PATH . 'Certs/alipay_public_key.pem',  //支付宝公钥
            'app_private_key_path' => CONF_PATH . 'Certs/app_private_key.pem'  //应用私钥
        ),
		/* 微信支付配置 */
		'wxpay' => array(		/* 尚软 */
			'appid' => 'wx7da3be6fb53cc23d',										/* 绑定支付的APPID（必须配置，开户邮件中可查看） */
			'mchid' => '1483873202',												/* 商户号（必须配置，开户邮件中可查看） */
			'key' => '98571771c5147a1b04b8fc9306f35311',							          /* 商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）;设置地址：https://pay.weixin.qq.com/index.php/account/api_cert */
			'appsecret' => 'd7d4a436b4f21ddb953295e3d65e127f',					/* APPSECRET：公众帐号secert（仅JSAPI支付的时候需要配置， 登录公众平台，进入开发者中心可设置），获取地址：https://mp.weixin.qq.com/advanced/advanced?action=dev&t=advanced/dev&token=2005451881&lang=zh_CN */
		    'ssl_cer' =>LIB_PATH.'Vendor/Wechat/Cert/apiclient_cert.pem',
            'ssl_key' =>LIB_PATH.'Vendor/Wechat/Cert/apiclient_key.pem'
        )
    ),
    'URL_ROUTER_ON' => true,
    'URL_ROUTE_RULES' => array(
        '/^login/' => 'Login/lists'
    ),
    // 腾讯QQ登录配置
    'THINK_SDK_QQ' => array(
        'APP_KEY' => '1106247948', // 应用注册成功后分配的 APP ID
        'APP_SECRET' => 'AzIAowKG4Nt60AAS', // 应用注册成功后分配的KEY
        'CALLBACK' => URL_CALLBACK . 'qq&url=' . URL_CALLBACK_1 . 'qq'
    ),
    // 新浪微博配置
    'THINK_SDK_SINA' => array(
        'APP_KEY' => '2818714930', // 应用注册成功后分配的 APP ID
        'APP_SECRET' => '64dd85a08c054d3081d2529b291c0dab', // 应用注册成功后分配的KEY
        'CALLBACK' => URL_CALLBACK . 'sina&url=' . URL_CALLBACK_1 . 'sina'
    ),
    // 微信登录
    'THINK_SDK_WEIXIN' => array(
        'APP_KEY' => 'wx5639343bad4b195d', // 应用注册成功后分配的 APP ID
        'APP_SECRET' => '2bc87ee0aeea63cba3c34c734fb85250', // 应用注册成功后分配的KEY
        'GrantType' => 'authorization_code',
        'CALLBACK' => URL_CALLBACK . 'weixin&url=' . URL_CALLBACK_1 . 'weixin'
    ),
    //redis 缓存
    'DATA_CACHE_PREFIX' => 'shunshou_',//缓存前缀
    'DATA_CACHE_TYPE'=>'Redis',//默认动态缓存为Redis
    'DATA_CACHE_TIME'=>'10',
    'REDIS_RW_SEPARATE' => true, //Redis读写分离 true 开启
    'DATA_REDIS_HOST'=>'127.0.0.1', //redis服务器ip，多台用逗号隔开；读写分离开启时，第一台负责写，其它[随机]负责读；
    'DATA_REDIS_PORT'=>'6379',//端口号
    'DATA_CACHE_TIME'=>300,//超时时间
    'DATA_PERSISTENT'=>false,//是否长连接 false=短连接
    'DATA_REDIS_AUTH'=>'7BdjvcM9kDHEuGUE',//AUTH认证密码

    'THINK_JIGUANG'=> array( //用户端秘钥
        'app_key'=>'f05f68b3cf8bb564c44460a1',
        'master_secret'=>'29019ee5ebe266a8ac2a6184',
    ),
);