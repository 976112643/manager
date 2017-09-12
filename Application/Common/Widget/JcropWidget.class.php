<?php
/**
 *	作品处理扩展
 */
namespace Common\Widget;

use Think\Controller;

/**
 * 作品处理扩展
 */
class JcropWidget extends Controller
{

    /**
     * 作品处理配置
     * 
     * @param $config 相关作品配置            
     * @param $id 要配置的作品编号            
     */
    public function index($config, $id)
    {
        $default_config = array(
            'index' => 2,
            'table' => '',
            'name' => 'cover',
            'width' => '260',
            'height' => '260',
            'scale' => '1'
        );
        $last_config = array_merge($default_config, $config);
        $data['config'] = $last_config;
        if ($last_config['table'] == '') {
            $this->error("请设置参数table");
        }
        if ($last_config['name'] == '') {
            $this->error("请设置参数name");
        }
        $data['info'] = get_info($last_config['table'], array(
            'id' => $id
        ), $last_config['name']);
        $data['config'] = $last_config;
        $this->assign($data);
        $this->display(T("Common@Jcrop/index"));
    }
}
