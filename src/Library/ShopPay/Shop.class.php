<?php
namespace app\Library\ShopPay;

/**
 *
 * @author dell
 *        
 */
class Shop extends Common
{
    /**
     * 电商订单信息统一处理
     * 2017年9月7日 上午11:58:58
     * @author liyee
     */
    public function shopInfo($url, $params = [], $method='GET'){
        return $this->shopRule($url, $params, $method);
    }
}