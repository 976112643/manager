<?php
/**
 *	文件扩展
 */
namespace Common\Widget;

use Think\Controller;

/**
 * 文件扩展类
 */
class FileWidget extends Controller
{

    /**
     * 文件扩展配置
     * 
     * @param $config 相关配置文件            
     * @param $id 要配置的文件编号            
     */
    public function index($config, $id)
    {
        if (isset($config['tpl'])) {
            if (! $config['tpl'])
                unset($config['tpl']);
        }
        $default_config = array(
            'index' => 1,
            'table' => '',
            'table_id' => 'id',
            'name' => '',
            'multi' => 'false',
            'val_key' => 'save_path',
            'max_num' => '',
            'tpl' => 'index'
        );
        $last_config = array_merge($default_config, $config);
        $data['config'] = $last_config;
        if ($last_config['table'] == '') {
            $this->error("请设置参数table");
        }
        if ($last_config['name'] == '') {
            $this->error("请设置参数name");
        }
        if ($config['multi'] != "true") {
            $info = get_info($last_config['table'], array(
                $last_config['table_id'] => $id
            ), $last_config['val_key']);
            $first = array_keys($info);
            $data['info'] = $info[$first[0]];
        }
        $data['id'] = $id;
        $this->assign($data);
        
        //dump($last_config);
        
        $this->display(T("Common@File/" . $last_config['tpl']));
    }
}
