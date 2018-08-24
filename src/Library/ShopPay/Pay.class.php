<?php
namespace app\Library\ShopPay;

/**
 *
 * @author dell
 *        
 */
class Pay extends Common
{
    
    /**
     * 电商订单支付信息统一处理
     * 2017年9月7日 上午11:58:58
     * @author liyee
     */
    public function payInfo($url, $params = [], $method='GET'){
        return $this->shopRule($url, $params, $method);
    }
}