<?php
/**
 * 底部导航
 * @package 
 * @author 秦晓武
 */
namespace Backend\Controller\Navigation;

/**
 * 底部导航
 */
class BottomController extends IndexController
{

    /**
     *
     * @var string 类型
     */
    protected $type = 'bottom';

    /**
     * 详情页，底部导航最多二级
     * 
     * @author 秦晓武
     */
    public function operate()
    {
        $data['info'] = get_info($this->table, array(
            'id' => I('ids')
        ));
        $all = get_result($this->table, array(
            'type' => $this->type
        ));
        $data['info']['pid'] = array_to_select($all, $data['info']['pid'], array(
            'limit' => 0
        ));
        $this->assign($data);
        $this->display('Navigation/operate');
    }
}
