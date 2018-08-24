<?php
namespace app\modules\wedding\services;

use app\helpers\myhelper;

class Subscription extends Service
{
    /**
     * 添加地址
     * @param array $data
     */
    public function create(Array $data)
    {
        $data = self::encode($data);
        $result = myhelper::sendRequest($data, 'POST', true, SHOP_API_URL . 'subscription/create');
        return (array)$result;
    }
}