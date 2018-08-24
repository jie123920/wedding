<?php
/**
 * 用户中心 - 授权验证
 * author:Tonly
 * date: 20160511
 * */
namespace Ucenter;

use Ucenter\Library\Common;


class Auth extends Common{

    /*
     * 构造函数
     * params:
     *  $url    string
     *  $domain string
     *  $expire int
     * */
    public function __construct( array $params ){
        parent::__construct($params);
    }


    /*
     * 验证用户登录合法性
     * params:
     *  $token     string  登录token
     * return:
     * */
    public function verify($token=null){
        $url = $this->url . '/auth/verify';
        if( null ===$token ){
            if( !$token = $this->getToken() ){
                return false;
            }
        }
        return $this->curl_http($url, array('token'=>$token));
    }

}
