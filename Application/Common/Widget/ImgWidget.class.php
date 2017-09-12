<?php
/**
 *	图片处理扩展
 */
namespace Common\Widget;

use Think\Controller;

/**
 * 图片处理扩展
 */
class ImgWidget extends Controller
{

    /**
     * 图片处理配置
     * 
     * @param $config 相关配置图片            
     * @param $id 要配置的图片编号            
     */
    public function index($config, $id)
    {
        $default_config = array(
            'index' => 1,
            'table' => '',
            'table_id' => 'id',
            'name' => '',
            'multi' => 'false',
            'val_key' => 'image',
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
        $this->display(T("Common@Img/" . $last_config['tpl']));
    }
}
