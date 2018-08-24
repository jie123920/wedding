<?php
namespace app\modules\api\controllers;
use Yii;
use app\Library\Mlog;

class StatController extends CommonController
{

    public function init() {
        parent::init();
        parent::behaviors();
    }

    public $enableCsrfValidation = false;//CSRF

    private $csidName = '_stat_csid';

    private $ucenter;

    public function actionSendMessage() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user_info = $this->getCookieUser();//判断用户是否登录
        if ( $user_info  && !empty($user_info['id'])) {
            $uid = $user_info['id'];
        } else {
            $uid = 0;
        }

        $cookies = Yii::$app->request->cookies;
        if ($cookies->has($this->csidName)) {
            $csid = $cookies->get($this->csidName)->value;
        } else {
            $csid = md5( uniqid( 'stat', true ) );
            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'name'   => $this->csidName,
                'value'  => $csid,
                'expire' => time() + 86400 * 7, // todo
                'domain' => \YII::$app->params['COOKIE_DOMAIN'],
            ]));
        }

        $sendmessage = new Mlog();

        $userAgent = Yii::$app->request->post('userAgent', $_SERVER['HTTP_USER_AGENT']);
        $refer     = Yii::$app->request->post('refer', $_SERVER['HTTP_REFERER']);
        $entry     = Yii::$app->request->post('entry', '');
        $ip        = Yii::$app->request->post('ip', \app\helpers\myhelper::get_client_ip());
        $action    = Yii::$app->request->post('action', '');
        $url       = Yii::$app->request->post('url', $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
        $tag       = Yii::$app->request->post('tag', '');

        $message = array(
            "timestamp" => time(),
            "uid"       => $uid,
            "csid"      => $csid,
            "userAgent" => $userAgent,
            "refer"     => $refer,
            "entry"     => $entry,
            "ip"        => $ip,
            "action"    => $action,
            "url"       => $url,
            "tag"       => $tag,
        );

        $res = $sendmessage->Send($message, 'shop');
        return $res;
    }

    protected function getCookieUser()
    {
        $this->ucenter = new \Ucenter\User(['env'=>ENV,'domain'=>DOMAIN]);

        if(!isset($_COOKIE['_ttl'] )){
            return false;
        }
        $userData = $ttl = null;
        if (isset($_COOKIE['_ttl'])) {//只存在1天
            $ttl = $_COOKIE['_ttl'];
        }

        if ($userData && $ttl == $userData['ttl']) {
            return $userData;
        }

        $userData = $this->ucenter->userinfo(null, 'id,email,country,username,avatar,gender,birth,skype,mobile,platform');
        if(($userData && $userData['code']!=0) || !$userData){
            return false;
        }
        $userData               = $userData['data'];
        $userData['avatar_url'] = $userData['avatar'];
        $userData['birth_data'] = strtotime($userData['birth']);
        $userData['ttl']        = $ttl;

        return $userData;
    }
}
