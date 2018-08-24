<?php
namespace app\modules\wedding\controllers;

use app\modules\shop\models\ShopCoupon;
use yii\web\Response;
use app\modules\shop\models\ShopOrderTemp;
use app\modules\shop\models\ShopOrder;

class CouponController extends CommonController
{

    public function init(){
        parent::init();
        \Yii::$app->response->format = Response::FORMAT_JSON;
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
        $request = \Yii::$app->request;
        $code = $request->get('code');
        $categories = $request->get('categories');
        $from_id = $request->get('from_id');
        $type = $request->get('type', 1);

        if ($code && $categories) {
            if ($this->nunByuid($code)){
                if (!is_array($categories)){
                    $categories = json_decode($categories, true);
                }
                $timestamp = time();
                
                $coupon = ShopCoupon::find()
                ->select(['category','promotion_type','allow_price','promotion_value'])
                ->where(['code' => $code,'status' => 1])
                ->andWhere(['<=','start_time',$timestamp])
                ->andWhere(['>=','end_time',$timestamp])
                ->andWhere(['>','use_times',0])
                ->one();
                
                if ($coupon) {
                    $category = json_decode($coupon->category, true); // 商品类别
                    $promotion_type = $coupon->promotion_type; // 优惠类型1:百分比，2：固定金额
                    $allow_price = $coupon->allow_price; // 满足金额
                    $promotion_value = $coupon->promotion_value; // 优惠额
                    $original_amount = 0.00;
                
                    foreach ($categories as $item){
                        $original_amount += $this->goodCoupon($item, $category);
                    }
                
                    if ($original_amount >= $allow_price) {
                        $promotion_amount = '';
                        switch ($promotion_type) {
                            case 2:
                                $promotion_amount = $promotion_value;
                                break;
                            default:
                                $promotion_amount = $original_amount * $promotion_value / 100;
                        }
                        if ($promotion_amount >= $original_amount){
                            $original_amount = $promotion_amount;
                        }
                
                        $promotion_amount = number_format($promotion_amount, 2);
                        $this->toOrderTemp($from_id, $code, $promotion_amount);
                        return $this->result(0, '', $promotion_amount);
                    } else {
                        return $this->result(1004, 'Code does not exist!');
                    }
                } else {
                    return $this->result(1003, 'Code does not exist!');
                }
            }else {
                return $this->result(1002, 'Code does not exist!');
            }
        } else {
            return $this->result(1001, 'Code does not exist!');
        }
    }
    
    /**
     * 针对每个物品返回优惠金额
     * 2017年5月24日 下午8:58:18
     * @author liyee
     */
    private function goodCoupon($good, $category){
        $amount = 0.00;        
        $intersect = array_intersect($good['categories'], $category);
        if ($intersect){
            $amount = $good['amount'];
        }
        
        return $amount;
    }
    
    /**
     * 优惠码和临时订单做关联
     * 2017年5月26日 下午3:47:48
     * @author liyee
     */
    private function toOrderTemp($from_id, $code, $promotion_amount){
        if ($code && $promotion_amount){
            $shopordertemp = ShopOrderTemp::findOne($from_id);
            if ($shopordertemp){
                $uid = $shopordertemp->uid;
                if ($this->getUid() == $uid){
                    $shopordertemp->coupon_code = $code;
                    $shopordertemp->coupon_amount = $promotion_amount;
                
                    $shopordertemp->save();
                }
            }
        }
    }
    
    /**
     * 根据用户id查询用户的优惠码是否使用
     * 2017年5月26日 下午8:42:43
     * @author liyee
     * @param unknown $from_id
     * @param unknown $code
     */
    private function nunByuid($code){
        $uid = $this->getUid();
        $status = [1,11,12,13];
        $shoporder = ShopOrder::find()->where(['coupon_code' => $code, 'uid'=>$uid])->andWhere(['in', 'status', $status])->exists();
        
        if (!$shoporder){
            return true;
        }else {
            return false;
        }
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

    public function actionTest(){
        
        $shopordertemp = ShopOrderTemp::findOne(35);
        
        echo $shopordertemp->uid;
        
        var_dump($shopordertemp);die;
        return false;
        $str = '{"41":{"sku_id":"41","amount":"207.30","categories":["1"]},"66":{"sku_id":"66","amount":"41.46","categories":["17"]}}';
        var_dump(json_decode($str, true));die;
        
        $categories = [
            '1' => [
                'sku_id' => 1,
                'amount' => 100.99,
                'categories' => [
                    2,3
                ],
            ],
            '2' => [
                'sku_id' => 2,
                'amount' => 200.66,
                'categories' => [
                    2
                ],
            ],
            '3' => [
                'sku_id' => 3,
                'amount' => 300.88,
                'categories' => [
                    2
                ],
            ],
        ];
        
        echo json_encode($categories);die;
        return $this->actionVerifyByCode('DB7777', $categories);
    }
}
