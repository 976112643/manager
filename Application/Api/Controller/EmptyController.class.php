<?php
/**
 * 无需写对应控制器，自动加载对应模版
 * @name 空控制器
 * @author 秦晓武
 * @time 2016-06-06
 */
namespace Api\Controller;

use Api\Controller\Base\BaseController;

/**
 * 无需写对应控制器，自动加载对应模版
 * 
 * @name 空控制器
 * @author 秦晓武
 *         @time 2016-06-06
 */
class EmptyController extends BaseController
{

    /**
     * 无需写对应ACTION，自动加载对应模版
     * 
     * @name 空方法
     * @author 秦晓武
     *         @time 2016-06-06
     */
    public function _empty()
    {
        $this->set_error('404:不存在的访问路径,请验证您的路劲是否准确');
    }
}
