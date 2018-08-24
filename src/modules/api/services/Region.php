<?php
namespace app\modules\api\services;

use app\helpers\myhelper;

class Region extends Service
{
    /**
     * 获取国家列表
     * @return array
     */
    public function countries()
    {
        $params = self::encode([]);
        $result = myhelper::sendRequest($params, 'GET', true, SHOP_API_URL . 'region/countries');
        return isset($result['data']) ? $result['data'] : [];
    }

    /**
     * 根据 region_id 获取区域信息
     * @param $region_id
     * @return array
     */
    public function one($region_id)
    {
        $params = ['region_id' => $region_id];
        $params = self::encode($params);
        $result = myhelper::sendRequest($params, 'GET', true, SHOP_API_URL . 'region/get');
        return isset($result['data']) ? $result['data'] : [];
    }

    /**
     * 根据 region_name 获取区域信息
     * @param $region_id
     * @return array
     */
    public function search($params = array())
    {
        $params = self::encode($params);
        $result = myhelper::sendRequest($params, 'GET', true, SHOP_API_URL . 'region/search');
        return isset($result['data']) ? $result['data'] : [];
    }
}