<?php
namespace app\modules\wedding\services;

use app\helpers\myhelper;

class UserNew extends Service
{
    public function create(Array $data)
    {
        $data = self::encode($data);
        $result = myhelper::sendRequest($data, 'POST', true, SHOP_API_URL . 'user-new/create');
        return (array)$result;
    }
    //wdx 0726
     public function update(Array $data)
    {
        $data = self::encode($data);
        $result = myhelper::sendRequest($data, 'POST', true, SHOP_API_URL . 'user-new/update');
        return (array)$result;
    }
}