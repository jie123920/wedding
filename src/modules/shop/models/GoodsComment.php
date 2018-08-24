<?php

namespace app\modules\shop\models;
use yii\helpers\ArrayHelper;
use Yii;
class GoodsComment extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{goods_comment}}';
    }
    public static function getDb() {
        return Yii::$app->get('db_shop');
    }

    public function scenarios() {
        return [
            'default' => ['id','shop_id','good_id','uid','star','create_time','content', 'picture','uname','avatar']
        ];
    }
    public function rules() {
        return [
            [['shop_id', 'good_id', 'uid', 'star', 'create_time'], 'integer'],
            [['content', 'picture','uname','avatar'], 'safe'],
            [['shop_id', 'good_id', 'uid', 'star', 'create_time','content'], 'required'],
            [['star'], 'integer','min'=>1, 'max' => 5],
        ];
    }

    //根据商品ID获取评论列表
    public static function Get($good_id,$page=1,$per_page = 100,$shop_id=SHOP_ID){
        $cacheKey = CACHE_PREFIX.'_good_reply_list_'.$good_id.'_'.$shop_id.'_'.$page.'_'.$per_page;
        if ($return = \Yii::$app->redis->hget(CACHE_PREFIX.'_good_reply_list_'.$good_id.'_'.$shop_id,$cacheKey)) {
             return unserialize($return);
        }

        $data = self::find()
            ->where(['good_id'=>$good_id,'shop_id'=>$shop_id])
            ->orderBy('is_hot_time DESC,create_time DESC')
            ->limit($per_page)
            ->offset(($page-1)*$per_page)
            ->asArray()
            ->all();
        $comment_count = GoodsComment::find()->where(['good_id'=>$good_id,'shop_id'=>$shop_id])->count();

        $domain = [
            1=>'clothesforever.com',
            2=>'lovecrunch.com'
        ];
        $cf_default_avatar = 'https://cdn-image.mutantbox.com/201709/26012560f9a806190a05f6d634bc22b1.png';


        $uids = ArrayHelper::getColumn($data,'uid');
        $ucenter = new \Ucenter\User(['env'=>ENV,'domain'=>$domain[$shop_id]]);
        $uInfos = $ucenter->userinfolistbyuid(implode(',',$uids));

        foreach ($data as & $_data){
            if(isset($uInfos[$_data['uid']])){
                $_data['uname'] = $uInfos[$_data['uid']]['username'];
                $_data['avatar'] = $uInfos[$_data['uid']]['avatar'];
            }
            if($_data['avatar'] == ''){
                $_data['avatar'] = $shop_id==1?$cf_default_avatar:DEFAULT_AVATAR;
            }
        }

        $result =  [
            'comment'=>$data,
            'total_count'=>$comment_count
        ];


        \Yii::$app->redis->hset(CACHE_PREFIX.'_good_reply_list_'.$good_id.'_'.$shop_id,$cacheKey, serialize($result));
        \Yii::$app->redis->expire(CACHE_PREFIX.'_good_reply_list_'.$good_id.'_'.$shop_id,CACHE_EXPIRE);


        return $result;
    }
}
