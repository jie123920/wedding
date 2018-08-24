<?php
namespace app\modules\shop\controllers;

use app\Library\ShopPay\ShopPay;

class OrderMobileController extends OCommonController
{
    public $defaultAction = 'index';
    public $payment_country_id;
    
    public function init()
    {
        parent::init();
        $this->enableCsrfValidation = false;
        if(!$this->is_login){
            return $this->result(10001);
        }
    }
    
    /**
     * 移动端方法适配
     * 2017年9月7日 下午7:09:30
     * @author liyee
     * @param unknown $subject
     * @param unknown $event
     * @param unknown $get
     * @return number[]|string[]|unknown[]|number[][]|string[][]|unknown[][]|array[]|mixed[]
     */
    public function actionRun(){
        $request = \Yii::$app->request;
        $get = $request->get();
        $post = $request->post();
        $params = array_merge($get, $post);
        if (isset($params['subject']) && isset($params['event'])){
            $subject = $params['subject'];
            $event = $params['event'];
            unset($params['subject']);
            unset($params['event']);
        }
        $shopPay = new ShopPay();
        $params = $this->addUid($event, $params);
        $params['lang'] = $this->getLang();
        $data = $shopPay->adap($subject, $event, $params);
        
        return $this->resultNew(json_decode($data, true));
    }
    
    /**
     * 添加用户id
     * 2017年9月7日 下午7:20:01
     * @author liyee
     * @param unknown $event
     * @param unknown $get
     * @return number
     */
    private function addUid($event, $params){
        $events = [
            'create-order',
            'direct-buy',
            'list'
        ];
        
        if (in_array($event, $events)){
            if (isset($this->user_info['id'])){
                $params['uid'] = $this->user_info['id'];
            }else {
                echo json_encode(['code'=>1001, 'data'=>[], 'Please login first']);die;
            }
        }
        return $params;
    }
    
    /**
     * 获取用户id
     *
     * @return int
     */
    private function getUid()
    {
        return $this->user_info['id'];
    }
    
    /**
     * 获取商店id
     *
     * @return int
     */
    private function getShopId()
    {
        // todo:
        return SHOP_ID;
    }
    
    /**
     * 获取语言
     *
     * @return string
     */
    private function getLang()
    {
        // todo:
        return LANG_SET;
    }
}
