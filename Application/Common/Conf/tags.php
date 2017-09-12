<?php
/**
 * 表单令牌生成配置
 * @package 
 */
return array(
    // 添加下面一行定义即可
    // 'view_filter' => array('Behavior\TokenBuild'),
    // 如果是3.2.1以上版本 需要改成
    'view_filter' => array(
        'Behavior\TokenBuildBehavior'
    ),
    // 配置多语言
    'app_begin' => array(
        'Behavior\CheckLangBehavior'
    )
);