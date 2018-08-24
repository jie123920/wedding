<?php
namespace app\modules\shop\controllers;
use app\modules\shop\services\Comment;
use app\modules\shop\models\Goods as G;
use app\modules\shop\models\GoodsComment;
use YII;
class CommentController extends CommonController
{
    public function init() {
        parent::init();
    }

    //评论动作
    public function actionComment(){
        $uid = \Yii::$app->request->get('uid',0);
        if(!$uid){
            return  $this->result(1,[],'uid is empty!');
        }
        $goods_id = Yii::$app->request->get('goods_id',0);
        $star = Yii::$app->request->get('star',0);
        $content = Yii::$app->request->get('content',0);
        $picture = Yii::$app->request->get('picture','');
        $shop_id = Yii::$app->request->get('shop_id',SHOP_ID);
        $uname = Yii::$app->request->get('uname','');
        $avatar = Yii::$app->request->get('avatar','');
        $goods_info = G::findOne($goods_id);
        if(!$goods_info){
            return  $this->result(1,[],'no this goods!');
        }

        $data = [
            'good_id'   =>$goods_id,
            'shop_id'   => $shop_id,
            'uid'       =>$uid,
            'uname'     =>$uname,
            'avatar'    =>$avatar,
            'star'      =>$star,
            'content'   =>$content,
            'picture'   =>$picture,
            'create_time'=>time()
        ];
        if(($error=Comment::insert($data)) !=1){
            return  $this->result(1,[],'error:'.$error);
        }else{
            return  $this->result(0,[],'comment successful');
        }
    }

    //评论列表
    public function actionCommentList(){
        $good_id = \yii::$app->request->get('goods_id',0);
        $shop_id = \yii::$app->request->get('shop_id',SHOP_ID);
        $model = G::One($good_id,0);
        if(!$model){
            return  $this->result(1,[],'no this good!');
        }
        $bread = $model['bread'];
        $name =  $model['good']['name'];
        $list =  GoodsComment::Get($good_id,1,100,$shop_id);
        $data = [
            'bread'=>$bread,
            'name'=>$name,
            'list'=>$list,
            'good_id'=>$good_id
        ];
        return  $this->result(0,$data,'get ok!');
    }

}
