<?php
/**
 * 微信设置父类
 */
namespace Backend\Controller\Weixin;

use Backend\Controller\Base\AdminController;
use Wechat\WechatTemp;

class IndexController extends AdminController
{
    /** 服务号操作对象*/
    protected $server;
    /** 企业号操作对象*/
    protected $company;
    
    /**
     * 服务号操作入口
     */
    protected function server_action($class,$action_name,$data)
    {
        $this->server = &load_wechat($class);
        return $this->server->$action_name($data);
    }
    /**
     * 企业号操作入口
     */
    protected function company_action($class,$action_name)
    {
        $this->company = &load_company_wechat($class);
        return $this->server->$action_name();
    }
}
?>