<?php
namespace app\modules\api\services;

use app\helpers\myhelper;

class UserAddress extends Service
{
    /**
     * 添加地址
     * @param array $data
     */
    public function create(Array $data)
    {
        $data = self::encode($data);
        $result = myhelper::sendRequest($data, 'POST', true, SHOP_API_URL . 'user-address/create');
        return (array)$result;
    }

    /**
     * 地址删除
     * @param array $data
     */
    public function delete(Array $data)
    {
        $data = self::encode($data);
        $result = myhelper::sendRequest($data, 'POST', true, SHOP_API_URL . 'user-address/delete');
        return (array)$result;
    }

    /**
     * 地址编辑
     * @param array $data
     */
    public function edit(Array $data)
    {
        $data = self::encode($data);
        $result = myhelper::sendRequest($data, 'POST', true, SHOP_API_URL . 'user-address/edit');
        return (array)$result;
    }

    /**
     * 地址列表
     * @param array $data
     */
    public function addressList(Array $data = [])
    {
        $data = self::encode($data);
        $result = myhelper::sendRequest($data, 'GET', true, SHOP_API_URL . 'user-address/list');
        return (array)$result;
    }

    /**
     * 设为默认
     * @param array $data
     */
    public function setDefault(Array $data = [])
    {
        $data = self::encode($data);
        $result = myhelper::sendRequest($data, 'POST', true, SHOP_API_URL . 'user-address/default');
        return (array)$result;
    }
}