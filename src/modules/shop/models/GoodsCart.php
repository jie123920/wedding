<?php

namespace app\modules\shop\models;

use Yii;
use yii\base\Exception;
use app\modules\wedding\services\Cart;
/**
 * This is the model class for table "cart".
 *
 * @property integer $id
 * @property integer $shop_id
 * @property integer $uid
 * @property integer $item_id
 * @property double $price
 * @property integer $number
 * @property integer $created_time
 * @property integer $updated_time
 */
class GoodsCart extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'goods_cart';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_shop');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'uid', 'item_id', 'price', 'number'], 'required'],
            [['shop_id', 'uid', 'item_id', 'number', 'created_time', 'updated_time'], 'integer'],
            [['price'], 'number'],
            [['custom_size','ads','temp_uid','http_referer','device','total_number','total_add'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'shop_id' => Yii::t('app', 'Shop ID'),
            'uid' => Yii::t('app', 'Uid'),
            'item_id' => Yii::t('app', 'Goods Item ID'),
            'price' => Yii::t('app', 'Goods Item Price'),
            'number' => Yii::t('app', 'Goods Number'),
            'created_time' => Yii::t('app', 'Created Time'),
            'updated_time' => Yii::t('app', 'Updated Time'),
        ];
    }


    public static function CartNum($uid){
        $number = 0;
        if(!$uid){
            $cookie_read = \YII::$app->request->cookies;
            if($cookie_read->has('cart')){
                $cookie_cart = $cookie_read->get('cart');
                $cookie_cart = unserialize($cookie_cart->value);
                if($cookie_cart)
                    $number = count($cookie_cart);
            }
            return $number;
        }
        $data = (new Cart())->cartCount(SHOP_ID, $uid);
        return $data;
    }


    //登录后如果COOKIE有购物车 则入库
    public static function init_cookie_cart($uid,$temp_uid=0){
        try {
            $cookie_read = \YII::$app->request->cookies;  
            if($cookie_read->has('cart')){
                $cart = $cookie_read->get('cart');  
                $cart_models = unserialize(gzdecode($cart->value));
                foreach ($cart_models as $cart_model){
                    if($uid){
                         $cartItem = GoodsCart::find()
                        ->where([ 'shop_id' => SHOP_ID,
                            'uid'  => $uid,
                            'item_id'=> $cart_model->item_id,
                        ])->one();
                    } 
                   //var_dump($cartItem);
                    if($cartItem){//如果数据库有次购物车商品 则更新数量
                        $cartItem->number = $cart_model->number;
                        $cartItem->uid = $uid;
                        if(!$cartItem->save()){
                            foreach ($cartItem->getErrors() as $key => $error) {
                                $exception = $error[0];
                                break;
                            }
                            throw new \Exception($exception);
                        }
                    }else{
                        if($temp_uid){
                            $cartItem = GoodsCart::find()
                            ->where([ 'shop_id' => SHOP_ID,
                                'temp_uid'  => $temp_uid,
                                'item_id'=> $cart_model->item_id
                            ])->one();
                            if($cartItem){
                                $cartItem->number = $cart_model->number;
                                $cartItem->uid = $uid;
                                if(!$cartItem->save()){
                                    foreach ($cartItem->getErrors() as $key => $error) {
                                        $exception = $error[0];
                                        break;
                                    }
                                    throw new \Exception($exception);
                                }
                            }
                        }else{
                            if(isset($cart_model->item_id)){
                                $item_model = GoodsSku::findOne($cart_model->item_id);//有此商品 才入库
                                if($item_model){
                                    $cart_model->uid = $uid;
                                    if(!$cart_model->save()){
                                        foreach ($cart_model->getErrors() as $key => $error) {
                                            $exception = $error[0];
                                            break;
                                        }
                                        throw new \Exception($exception);
                                    }
                                }
                            }
                        }
                       
                        $cart_model = null;
                    }
                    //  if($temp_uid)
                    // GoodsCart::deleteAll(['temp_uid' => $temp_uid, 'shop_id' => SHOP_ID, 'item_id' => $cart_model->item_id]);
                }
                YII::$app->response->cookies->remove(new \yii\web\Cookie([
                    'name'  => 'cart',
                    'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
                ]));
                // YII::$app->response->cookies->remove('cart');
                
                \Yii::$app->redis->del(CACHE_PREFIX."_cart_num_".$uid);
            }
        } catch (\Exception $e ) {
            YII::error( 'init_cookie_cart:'.$e->getFile() . ':' . $e->getLine() . ':' . $e->getMessage() );
        }
    }

    public static function getCartOne($shop_id, $uid, $item_id)
    {
        return GoodsCart::find()
            ->where(['shop_id' => $shop_id ])
            ->andWhere(['uid'  => $uid ])
            ->andWhere(['item_id' => $item_id ])
            ->one();
    }

    public function updateCart(Array $data)
    {
        $this->setAttributes($data);
        $this->updated_time = time();
        return $this->save();
    }

    public function addToCart(Array $data)
    {
        $this->setAttributes($data);
        $this->created_time = $this->updated_time = time();
        return $this->save();
    }

    public function getCartAll($shop_id, $uid)
    {
        return GoodsCart::find()
            ->where([ 'shop_id'  => $shop_id ])
            ->andWhere([ 'uid'  => $uid ])
            ->orderBy('id desc')
            ->asArray()
            ->indexBy('item_id')
            ->all();
    }
}
