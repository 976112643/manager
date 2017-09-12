<?php
namespace Backend\Controller\Capital;

/**
 * 用户账户
 * 
 * @author 秦晓武
 *         @time 2016-10-11
 */
class WalletController extends IndexController
{
    /** 操作表*/
    protected $table = 'member';

    /**
     * 列表
     */
    public function index()
    {
        $map = array();
        /**
         * 手机
         */
        if (strlen(trim(I('mobile')))) {
            $map['mobile'] = array(
                'like',
                '%' . I('mobile') . '%'
            );
        }
        /**
         * 邮箱
         */
        if (strlen(trim(I('email')))) {
            $map['email'] = array(
                'like',
                '%' . I('email') . '%'
            );
        }
        /**
         * 昵称
         */
        if (strlen(trim(I('nickname')))) {
            $map['nickname'] = array(
                'like',
                '%' . I('nickname') . '%'
            );
        }
        $sort = array(
            'mobile',
            'nickname',
            'email'
        );
        $order = $this->get_order($sort);
        $result = $this->page($this->table, $map, $order);
        $this->assign($result);
        $this->display();
    }
}

