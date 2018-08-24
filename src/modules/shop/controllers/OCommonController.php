<?php
namespace app\modules\shop\controllers;
use Yii;
use app\modules\lovecrunch\controllers\CommonController as Comm;
use app\modules\shop\models\ShopOrder;

class OCommonController extends Comm {
    public $callback = null;

    public function init(){
        parent::init();
        $this->callback = Yii::$app->request->get('callback',null);
        Yii::$app->response->format = $this->callback ? \yii\web\Response::FORMAT_JSONP : \yii\web\Response::FORMAT_JSON;
    }

    public function result($code=0,$data = [],$msg=''){
        if($this->callback){
            return array(
                'callback' => $this->callback,
                'data' => [
                    'code' => $code,
                    'message' => $msg,
                    'data'=>$data
                ]
            );
        }else{
            return array(
                    'code' => $code,
                    'message' => $msg,
                    'data'=>$data
                );
        }
    }
    
    public function resultNew($data){
        if (isset($data['code']) && ($data['code'] == 0)){
            return $this->result(0, $data['data'], 'success');
        }elseif (isset($data['code'])){
            return $this->result($data['code'], $data['data'], $data['message']);
        }else {
            return $this->result(1, [], 'error');
        }
    }
    
    /**
     * 根据用户id查询用户的优惠码是否使用
     * 2017年5月26日 下午8:42:43
     * @author liyee
     * @param unknown $from_id
     * @param unknown $code
     */
    protected function nunByuid($code, $uid){
        $status = [1,11,12,13];
        $shoporder = ShopOrder::find()->where(['coupon_code' => $code, 'uid'=>$uid])->andWhere(['in', 'status', $status])->exists();
        
        if (!$shoporder){
            return true;
        }else {
            return false;
        }
    }


    public  function auth_user($_ttl,$shop_id=SHOP_ID){
        $cookie = isset($_COOKIE['_ttl'])?$_COOKIE['_ttl']:'';
        $_ttl = $_ttl?$_ttl:$cookie;//TODO
        if(!in_array($shop_id,[1,2])){
            return false;
        }
        if($shop_id == 1){
            $domain = 'clothesforever.com';
        }else{
            $domain = 'lovecrunch.com';
        }

        $ucenter = new \Ucenter\User(['env'=>ENV,'domain'=>$domain]);
        $res = $ucenter->getTokenByTtl($_ttl);
        if($res){
            $res = json_decode($res,true);
            $userData = $ucenter->userinfo($res['token'], 'id,email,country,username,avatar,gender,birth,skype,mobile,platform');
            if($userData && $userData['code'] == 0){
                return $userData['data'];
            }
        }
        return false;
    }
}
