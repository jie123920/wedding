<?php
namespace app\modules\shop\controllers;

class CouponController extends OrderInternalsController
{

    public function init()
    {
        parent::init();
        $this->enableCsrfValidation = false;
        if(!$this->is_login){
            return $this->result(10001,[],'please log in first!');
        }
    }

    public function actionIndex(){
        return $this->render('index');
    }

    /**
     * 验证code并返回优惠金额
     * 2017年5月24日 下午3:11:44
     * 
     * @author liyee
     * @param string $code            
     * @param number $type            
     */
    public function actionVerifyByCode($code = '', $categories = '', $from_id = '', $type = 1){
        $uid = $this->getUid();
        return $this->VerifyByCode($uid, $code, $categories, $from_id, $type);
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

}
