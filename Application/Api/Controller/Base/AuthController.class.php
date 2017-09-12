<?php

namespace Api\Controller\Base;
/**
 * API设置权限验证类
 */
class AuthController extends BaseController
{
    /**
     * API接口基础类-基础构造函数
     */
    protected function __autoload()
    {
        parent::__autoload();
        $this->is_auth();
        $this->__init();
    }

    /**
     * 子类集成
     * 
     * @return [type] [description]
     */
    protected function __init()
    {}
}