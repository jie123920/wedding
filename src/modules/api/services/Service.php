<?php
namespace app\modules\api\services;

class Service
{
     public static $key = '1Fq9uZj9JeJPuje2';
     //接口加密
     public static function encode($params){
         $post = $params ;
         sort($params,SORT_STRING);
         $sign = md5(self::$key. implode("", $params));
         $post['signature'] = $sign;
         return $post;
     }
}