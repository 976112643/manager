<?php
namespace Think\Pay\Driver;

class Jdpay extends \Think\Pay\Pay
{

    protected $gateway = 'https://h5pay.jd.com/jdpay/saveOrder';
    // protected $gateway = 'https://wepay.jd.com/jdpay/saveOrder';
    protected $config = array(
        'merchant' => '',
        'desKey' => '',
        'callback_url' => '',
        'notify_url' => ''
    );

    public function check()
    {
        if (! $this->config['merchant'] || ! $this->config['desKey']) {
            E("京东设置有误！");
        }
        return true;
    }

    public function buildRequestForm(\Think\Pay\PayVo $vo)
    {
        vendor('Jdpay.Util.SignUtil', '', '.class.php');
        vendor('Jdpay.Util.TDESUtil', '', '.class.php');
        
        $param = array(
            'device' => '',
            'tradeNum' => $vo->getOrderNo(),
            'tradeName' => $vo->getTitle(),
            'tradeDesc' => '',
            'tradeTime' => date('YmdHis'),
            'amount' => (string) ($vo->getFee() * 100),
            'currency' => 'CNY',
            'note' => '',
            'notifyUrl' => $vo->getCallback(),
            'callbackUrl' => $vo->getUrl(),
            'ip' => '',
            'specCardNo' => '',
            'specId' => '',
            'specName' => '',
            'userType' => 'BIZ',
            'userId' => time() . session('member_id'),
            'expireTime' => '',
            'orderType' => '0',
            'industryCategoryCode' => ''
        );
        /* 生成签名 */
        $param['version'] = 'V2.0';
        $param['merchant'] = $this->config['merchant'];
        $sign = \SignUtil::signWithoutToHex($param, array(
            'sign'
        ));
        /* 加密参数 */
        $desKey = $this->config['desKey'];
        $keys = base64_decode($desKey);
        $except = [
            'version',
            'merchant'
        ];
        foreach ($param as $key => $value) {
            if (in_array($key, $except))
                continue;
            $param[$key] = \TDESUtil::encrypt2HexStr($keys, $value);
        }
        $param['sign'] = $sign;
        $param['subject'] = $vo->getTitle();
        $param['body'] = '支付成功后请不要关闭窗口，等待自动跳转';
        // dump($param);exit;
        $sHtml = $this->_buildForm($param, $this->gateway);
        
        return $sHtml;
    }

    /**
     * 针对notify_url验证消息是否是京东发出的合法消息
     *
     * @return 验证结果
     */
    public function verifyNotify($notify = [])
    {
        // $notify = $GLOBALS['HTTP_RAW_POST_DATA'];
        $resdata = [];
        vendor('Jdpay.Util.XMLUtil', '', '.class.php');
        $flag = \XMLUtil::decryptResXml($notify, $resdata);
        $this->info = $resdata;
        return $flag;
    }

    /**
     * 异步通知验证成功返回信息
     */
    public function notifySuccess()
    {
        echo 'ok';
        exit();
    }
}
