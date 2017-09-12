<?php
return array(
	/*复写SESSION前缀，避免和前台冲突*/
	'SESSION_PREFIX' => 'sc_backend_',
	
	/* 模板相关配置 */
	'TMPL_PARSE_STRING' => array(
        '__STATIC__' => __ROOT__ . '/Public/Static',
        '__PLUGIN__' => __ROOT__ . '/Public/Plugins',
        '__IMG__' => __ROOT__ . '/Public/' . MODULE_NAME . '/img',
        '__CSS__' => __ROOT__ . '/Public/' . MODULE_NAME . '/css',
        '__JS__' => __ROOT__ . '/Public/' . MODULE_NAME . '/js'
    ),
	/* 默认控制器 */
	'DEFAULT_CONTROLLER' => 'Index',
	/* 配置类型 */
	'config_type' => array(
        'text' => '文本框',
        'textarea' => '文本域',
        'radio' => '单选框',
        'checkbox' => '复选框',
        'select' => '下拉框',
        'editor' => '编辑器',
        'image' => '图片'
    ),
);