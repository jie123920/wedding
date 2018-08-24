<?php
namespace app\helpers;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/22
 * Time: 16:19
 */
class sendEmail{
    /**
     * 向用户发送邮件
     * 2017年6月8日 下午3:55:42
     * @author liyee
     * @param string $type
     * @param unknown $params
     * @return boolean
     */
    public static function run($type = '', $info = []){
        $var = false;
        if (isset($info['email']) && $info['email']){
            $email = $info['email'];
            $url = \Yii::$app->params['edm'].'/message/generate';
            $params = [
                'app_id' => '782937352627475',
                'type' => 'email',
                'timestamp' => time(),
            ];
            $arr = [];
            foreach($params as $key => $value) {
                $arr[] = $key . $value;
            }
            sort($arr, SORT_STRING);
            $secretKey = 'c227a43454a2fcac3fbb0d9ce8d8cfa7';
            $params['signature'] = md5($secretKey . implode("", $arr));

            $list = self::contentList($type, $info);
            $params['data']['from'] = $list['from'];
            $params['data']['subject'] = $list['subject'];    
            $params['data']['to'] = $email;    
            $params['data']['html'] = $list['content'];
    
            $params_str = json_encode($params);    
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS,$params_str);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params_str))
            );
    
            $result = curl_exec($ch);
            if ($result){
                myhelper::inlog('sendEmail', 'run', $info['id'].':'.$result);
                $arr = json_decode($result, true);
                if (is_array($arr) && ($arr['code'] == 0)){
                    $var = true;
                }
            }
        }
        return $var;
    }
    
    /**
     * 邮件内容
     * 2017年6月8日 下午2:46:35
     * @author liyee
     */
    private static function contentList($type = 'order_seccess', $info){
        $orderid = isset($info['id'])?$info['id']:'';
        $email = $info['email'];
        $products = isset($info['products'])?$info['products']:'';
        $products = self::products($products);
        $freight = isset($info['freight'])?$info['freight']:'';
        $total_amount = isset($info['total_amount'])?$info['total_amount']:'';
        $project_id = isset($info['project_id'])?$info['project_id']:'7';
        
        $list = [
            'order_seccess' => [
                '7' =>[
                    'from' => 'Clothes Forever <postmaster@clothesforevermail.com>',
                    'subject' => "ClothesForever Order Confirmation- Order #$orderid",
                    'content' => "Dear $email<br/>
                    Thank you for shopping at ClothesForever. Here are the details of your order: <br/>
                    <br/>
                    ORDER NUMBER#$orderid
                    <br/>
                    $products
                    <br/>
                    Delivery Fee: $$freight<br/>
                    Total: $$total_amount<br/>
                    <br/>
                    Please do not reply to this email.<br/>
                    All charges are processed in U.S. currency. <br/>
                    Thank you for shopping at ClothesForever<br/>
                    Visit us again at <a href='http://www.clothesforever.com' target='_blank'>www.clothesforever.com</a>",
                ],
                '7_2' =>[
                    'from' => 'Love Crunch <postmaster@lovecrunchmail.com>',
                    'subject' => "LoveCrunch Order Confirmation- Order #$orderid",
                    'content' => "Dear $email<br/>
                    Thank you for shopping at LoveCrunch. Here are the details of your order: <br/>
                    <br/>
                    ORDER NUMBER#$orderid
                    <br/>
                    $products
                    <br/>
                    Delivery Fee: $$freight<br/>
                    Total: $$total_amount<br/>
                    <br/>
                    Please do not reply to this email.<br/>
                    All charges are processed in U.S. currency. <br/>
                    Thank you for shopping at LoveCrunch<br/>
                    Visit us again at <a href='http://www.lovecrunch.com' target='_blank'>www.lovecrunch.com</a>",
                ],    
            ],
            'order_delivered' => [
                '7' => [
                    'from' => 'Clothes Forever <postmaster@clothesforevermail.com>',
                    'subject' => "ClothesForever Shipment Confirmation - Order #$orderid",
                    'content' => 'order_delivered',
                ],
                '7_2' => [
                    'from' => 'Love Crunch <postmaster@lovecrunchmail.com>',
                    'subject' => "LoveCrunch Shipment Confirmation - Order #$orderid",
                    'content' => 'order_delivered',
                ],                
            ],
        ];
        
        if (isset($list[$type][$project_id])){
            return $list[$type][$project_id];
        }else {
            return [
                'from' => '',
                'subject' => '',
                'content' => '',
            ];
        }
    }
    
    /**
     * 解析产品信息
     * 2017年6月8日 下午4:33:40
     * @author liyee
     * @param string $data
     */
    private static function products($products = '') {
        $arr = json_decode($products, true);
        
        $data = '';
        foreach ($arr as $item){
            $data .= 'Item: '.$item['name'].'<br/>';
            $data .= 'Qty: '.$item['number'].'<br/>';
            $data .= 'Price: $'.$item['price'].'<br/>';
            unset($item['goods_sku_id']);
            unset($item['goods_id']);
            unset($item['price']);
            unset($item['name']);
            unset($item['number']);
            unset($item['amount']);
        
            foreach ($item as $k=>$v){
                $data .= ucfirst($k).': '.$v.'<br/>';
            }
        }
        
        return $data;
    }
    
}