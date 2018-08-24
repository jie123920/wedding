<?php
namespace app\modules\shop\services;
use app\modules\shop\models\GoodsComment;
use app\modules\shop\models\Goods as G;
class Comment
{
    public static function insert($data){
        $GoodsComment = new GoodsComment;
        $GoodsComment->isNewRecord = true;
        $GoodsComment->setAttributes($data);
        if(!$GoodsComment->save($data)){
            foreach ($GoodsComment->getErrors() as $key=>$error){
                $exception = $error[0];break;
            }
            return  $exception;
        }
        return  1;
    }
}