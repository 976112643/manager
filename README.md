需要准备的东西

#定时任务
1、/Api/Refund/Paypush/index   //订单超期退款通知，每分钟执行一次
2、/Api/Refund/Paypush/over    //未支付订单超期关闭  每分钟执行一次

#需要开启的扩展
1、curl
2、redis 端口6379
3、OpenSSL

#需要修改的PHP配置
1、PHP上传大小改为 128M
2、PHP post数据大小  改为  128M