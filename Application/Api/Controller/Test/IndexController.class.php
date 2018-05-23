<?php
namespace Api\Controller\Test;

use Api\Controller\Base\BaseController;
use Common\Plugin\RSA;
/**
 * 默认首页空控制器
 * @author Administrator
 */
class IndexController extends BaseController
{
  

    public function index()
    {
		$descrypt=new RSA(CONF_PATH . 'Certs/api_public_key.txt');
		//echo CONF_PATH . 'Certs/api_public_key.txt';
		$post=I('get.');
      //decrypt
		SUCCESS($descrypt->pubdecrypt($post['sign']));
    }
}

?>