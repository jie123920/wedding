<?php
namespace app\modules\wedding\controllers;
use app\modules\wedding\services\UserNew;
use app\modules\wedding\services\Region;
use Ucenter\Ucenter;
use app\helpers\myhelper;
use app\Library\Net\IpLocation;
use app\Library\Mlog;

class UcenterController extends CommonController {
    public $ucenter = NULL;
    private $result = array('code' => 0, 'error' => '', 'data' => array());

    public function init(){
        parent::init();
        preg_match("/^(http[s]?:\/\/)?([^\/]+)/i", isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'', $matches);
        header('Access-Control-Allow-Origin:' . $matches[0]);
        header("Access-Control-Allow-Credentials: true");
        $this->ucenter = new Ucenter(['domain'=>DOMAIN, 'env'=>ENVUC]);
    }

    public function actionRegister($email='', $password='', $adv_key = '') {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $email = $email ? $email : \Yii::$app->request->post('newemail','');
        $email = trim($email);
        $password = $password ? $password : \Yii::$app->request->post('newpassword','');
        //$adv_key = $adv_key ? $adv_key : \Yii::$app->request->post('adv_key','');
        //$http_referers = \Yii::$app->request->post('http_referer','');
        $http_referers = '';
        if($this->cookies->has("http_referer"))
        $http_referers =$this->cookies->get("http_referer","")->value;
        $ads = '';
        if($this->cookies->has("ads"))
        $ads =$this->cookies->get("ads","")->value;
        $adv_key = '';
        if($this->cookies->has("adv_key"))
        $adv_key =$this->cookies->get("adv_key","")->value;
        $adv_key = $adv_key?$adv_key.'-PC':'';
        $clientip = myhelper::get_client_ip();

        $returnData = $this->actionCheckuser($email, 'entity');

        if($returnData['data'] == true || !myhelper::grepcheck($email)) {
            $this->result['code'] = 1000;
            return $this->result;
        }
        if( !$password ){
            $this->result['code'] = 1001;
            return $this->result;
        }

        $params = array();
        $params['account'] = $email;
        $params['password'] = $password;
        $params['ip'] = $clientip;
        $params['ads_key'] = $adv_key;

        //调用用户中心
        $returnData = $this->ucenter->Register()->register($params);

        //发送日志
        $sendmessage = new Mlog();
        $sendmessage->inlog('ucenter', 'register_data', $returnData['data']);
        if (empty($returnData['data'])){
            $sendmessage->inlog('ucenter', 'register_server', $_SERVER);
        }

        $mainref = !isset($adv_key)?'':'adv';

        if (isset($returnData['data']['uid'])){
            $http_referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
            $http_referer = substr($http_referer, 0, 1000);

            $message = array(
                'timestamp' => time(),
                'tpuid' => '',
                'uid' => $returnData['data']['uid'],
                'mainref' => $mainref,
                'subref' => $adv_key,
                'entry' => 'gw',
                'count' => '1',
                'ip' => $clientip,
                'step' => '3',
                'appid' => '4',
                'ref' => $http_referer,
            );
            $sendmessage->Send($message, 'adv');

            $new_user_data = [
                'time' => time(),
                'uid'=>$returnData['data']['uid'],
                'email'=>$returnData['data']['email'],
                'platform'=>'bycouturier',
                'shop_id'=>SHOP_ID,
                'type'=>'gw',
                'ip'=>$clientip,
                'ref'=>$http_referer,
                'device'=> $_SERVER['HTTP_USER_AGENT'],
                'http_referer'=>$http_referers,
                'adv_key'=>$adv_key,
                'ads'=>$ads,
                 
            ];
            (new UserNew())->create($new_user_data);

        }

        $this->sessions['userinfo'] = $returnData['data'];

        //注册后生成所用的cookie信息
        $this->createcookie($returnData['data']);

        if( $returnData['code'] ){
            $this->result['code'] = 1002;
            $this->result['data'] = $returnData;
            return $this->result;
        }

        $this->cookies_2->add(new \yii\web\Cookie([
            'name' => 'last_update_time',
            'value' => time(),
            'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
        ]));
        $this->sessions['user_data'] = null;

        //设置国家
        $this->setCountry($returnData['data']['token']);

        //RETURN
        $this->result['data'] = $returnData['data'];
        return $this->result;
    }

    public function actionLogin() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $email = trim(\Yii::$app->request->post('email',''));
        $password =\Yii::$app->request->post('password','');
        $remeber_time =\Yii::$app->request->post('remeber_time','');
        if( !myhelper::grepcheck($email)){
            $this->result['code'] = 1005;
            return $this->result;
        }
        if( !$password ){
            $this->result['code'] = 1006;
            return $this->result;
        }

        //调用用户中心
        $returnData = $this->ucenter->Login()->loginGW($email, $password,$remeber_time);
        if( !$returnData ){
            $this->result['code'] = 1007;
            return $this->result;
        }
        if( $returnData['code'] ){
            $this->result['code'] = 1008;
            $this->result['data'] = $returnData;
            return $this->result;
        }
        $this->sessions['userinfo'] = $returnData['data'];
        $data = $returnData['data'];

        //登录成功后生成所用的cookie信息
        $this->createcookie($data,$remeber_time);
        $this->cookies_2->add(new \yii\web\Cookie([
            'name' => 'last_update_time',
            'value' => time(),
            'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
        ]));
        $this->sessions['user_data'] = null;

        //RETURN
        $this->result['data'] = $data;

        return $this->result;
    }


    /* 登录-FB */
    public function actionLoginfb() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if( !$app_type = \Yii::$app->request->post('app_type','')){
            $this->result['code'] = 1010;
            $this->result['error'] = 'app_type is empty';
            return $this->result;
        }
        if( !$token = \Yii::$app->request->post('token','') ){
            $this->result['code'] = 1011;
            $this->result['error'] = 'token is empty';
            return $this->result;
        }

        $adv_tag = \Yii::$app->request->post('adv_tag','');

        //调用用户中心
        $returnData = $this->ucenter->Login()->loginFB($token, $app_type, $adv_tag);
        if( !$returnData ){
            $this->result['code'] = 1012;
            $this->result['error'] = 'error';
            return $this->result;
        }
        if( $returnData['code'] ){
            $this->result['code'] = 1013;
            $this->result['data'] = $returnData;
            $this->result['error'] = $returnData['error'];
            return $this->result;
        }

        $this->sessions['userinfo'] = $returnData['data'];
        $data = isset($returnData['data'])?$returnData['data']:'';

        //打印fb返回信息
        $mlog = new Mlog();
        $mlog->inlog('ucenter', 'loginfb_data', $data);

        $lasttime = isset($data['lasttime'])?$data['lasttime']:'';
        $createtime = isset($data['createtime'])?$data['createtime']:'';
        $regdate = isset($data['regdate'])?$data['regdate']:'';
        $third_id = isset($data['third_id'])?$data['third_id']:'';

        if (isset($returnData['data']['uid']) && !empty($returnData['data']['uid']) && !empty($lasttime) && !empty($regdate) && ($lasttime == $regdate)){
            //发送日志
            $sendmessage = new Mlog();
            $sendmessage->inlog('ucenter', 'lasttime-createtime', $lasttime.'-'.$regdate);

            $mainref = !isset($adv_tag)?'':'adv';

            $http_referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
            $http_referer = substr($http_referer, 0, 1000);

            $message = array(
                'timestamp' => time(),
                'tpuid' => $third_id,
                'uid' => $returnData['data']['uid'],
                'mainref' => $mainref,
                'subref' => $adv_tag,
                'entry' => 'gw',
                'count' => '1',
                'ip' => myhelper::get_client_ip(),
                'step' => '3',
                'appid' => '4',
                'ref' => $http_referer,
            );
            $sendmessage->Send($message, 'adv');



            $new_user_data = [
                'time' => time(),
                'uid'=>$returnData['data']['uid'],
                'email'=>$returnData['data']['email'],
                'platform'=>'bycouturier',
                'shop_id'=>SHOP_ID,
                'type'=>'facebook',
                'ip'=>myhelper::get_client_ip(),
                'ref'=>$http_referer,
                'device'=> $_SERVER['HTTP_USER_AGENT']
            ];
            (new UserNew())->create($new_user_data);
        }

        //登录成功后生成所用的cookie信息
        $this->createcookie($data);
        //设置国家
        $userData = (new \Ucenter\User(['env'=>ENV]))->userinfo(null, 'country');
        if($userData && empty($userData['data']['country'])){
            $this->setCountry($returnData['data']['token']);
        }
        $this->cookies_2->add(new \yii\web\Cookie([
            'name' => 'last_update_time',
            'value' => time(),
            'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
        ]));
        $this->sessions['user_data'] = null;

        //RETURN
        $this->result['data'] = $data;
        return $this->result;
    }


    /* 登录-GOOGLE */
    public function actionLogingg() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if( !$code = \Yii::$app->request->post('code','')){
            $this->result['code'] = 1015;
            $this->result['error'] = 'code is empty';
            return $this->result;
        }

        if( !$app_type = \Yii::$app->request->post('app_type','')){
            $app_type = "mutantbox";
        }
        $adv_tag = \YII::$app->request->post('adv_tag','');

        //调用用户中心
        $returnData = $this->ucenter->Login()->loginGG($code, $app_type, $adv_tag);
        if( !$returnData ){
            $this->result['code'] = 1016;
            $this->result['error'] = 'error';
            return $this->result;
        }
        if( $returnData['code'] ){
            $this->result['code'] = 1017;
            $this->result['data'] = $returnData;
            $this->result['error'] = $returnData['error'];
            return $this->result;
        }

        $this->sessions['userinfo'] = $returnData['data'];
        $data = isset($returnData['data'])?$returnData['data']:'';

        //打印gg返回信息
        $mlog = new Mlog();
        $mlog->inlog('ucenter', 'logingg_data', $data);

        $lasttime = isset($data['lasttime'])?$data['lasttime']:'';
        $createtime = isset($data['createtime'])?$data['createtime']:'';
        $regdate = isset($data['regdate'])?$data['regdate']:'';
        $third_id = isset($data['third_id'])?$data['third_id']:'';

        if (isset($returnData['data']['uid']) && !empty($returnData['data']['uid']) && !empty($lasttime) && !empty($regdate) && ($lasttime == $regdate)){
            //发送日志
            $sendmessage = new Mlog();
            $sendmessage->inlog('ucenter', 'lasttime-createtime', $lasttime.'-'.$regdate);

            $mainref = !isset($adv_tag)?'':'adv';

            $http_referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
            $http_referer = substr($http_referer, 0, 1000);

            $message = array(
                'timestamp' => time(),
                'tpuid' => '',
                'uid' => $returnData['data']['uid'],
                'mainref' => $mainref,
                'subref' => $adv_tag,
                'entry' => 'gw',
                'count' => '1',
                'ip' => myhelper::get_client_ip(),
                'step' => '3',
                'appid' => '4',
                'ref' => $http_referer,
            );
            $sendmessage->Send($message, 'adv');



            $new_user_data = [
                'time' => time(),
                'uid'=>$returnData['data']['uid'],
                'email'=>$returnData['data']['email'],
                'platform'=>'bycouturier',
                'shop_id'=>SHOP_ID,
                'type'=>'google',
                'ip'=>myhelper::get_client_ip(),
                'ref'=>$http_referer,
                'device'=> $_SERVER['HTTP_USER_AGENT']
            ];
            (new UserNew())->create($new_user_data);
        }

        //登录成功后生成所用的cookie信息
        $this->createcookie($data);

        //设置国家
        $userData = (new \Ucenter\User(['env'=>ENV]))->userinfo(null, 'country');
        if($userData && empty($userData['data']['country'])){
            $this->setCountry($returnData['data']['token']);
        }

        $this->cookies_2->add(new \yii\web\Cookie([
            'name' => 'last_update_time',
            'value' => time(),
            'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
        ]));
        $this->sessions['user_data'] = null;

        $this->result['data'] = $returnData['data'];
        return $this->result;
    }


    /**
     * 用户退出登录
     *2016年5月5日 下午4:24:06
     */
    public function actionLogout() {

        setcookie('user','',time()-100,'/',DOMAIN);

        $token = $this->sessions['userinfo']['token'];
        if (isset($token)){
            $this->ucenter->User()->logout($token);
        }
        $this->sessions['userinfo'] = null;
        $this->cookies_2->add(new \yii\web\Cookie([
            'name' => '_ttl',
            'value' => null,
            'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
            'expire'=>time()-1
        ]));
        $this->cookies_2->add(new \yii\web\Cookie([
            'name' => 'remember_me_token',
            'value' => null,
            'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
            'expire'=>time()-1
        ]));
        $this->cookies_2->add(new \yii\web\Cookie([
            'name' => 'user_auth',
            'value' => null,
            'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
            'expire'=>time()-1
        ]));
        $this->cookies_2->add(new \yii\web\Cookie([
            'name' => 'user_region',
            'value' => null,
            'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
            'expire'=>time()-1
        ]));
        $this->cookies_2->add(new \yii\web\Cookie([
            'name' => 'user_auth_sign',
            'value' => null,
            'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
            'expire'=>time()-1
        ]));
        if (isset($_SERVER['HTTP_REFERER'])) {
            $this->redirect($_SERVER['HTTP_REFERER']);
        }else{
            $this->redirect('/');
        }
    }


    public function actionCheckuser($email='',$type=''){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $email = $email ? $email : \Yii::$app->request->post('email','');
        $email = $email ? $email : \Yii::$app->request->post('newemail','');
        $type = $type ? $type : \Yii::$app->request->post('type','');
        $returnData = $this->ucenter->User()->account(trim($email));
        if( !$returnData ){
            $this->result['code'] = 1025;
            $this->result['error'] = '1025';
            $this->result['msg'] = \Yii::t('common','validEmail');
            return $this->result;
        }
        if( $returnData['code']){
            $this->result['code'] = 1026;
            $this->result['error'] = '1026';
            $this->result['msg'] = \Yii::t('common','validEmail');
            return $this->result;
        }

        $data = $returnData['data'];

        if ($data){
            if ($type == "checked") {
                return true;
            }elseif ($type == 'entity'){
                return $returnData;
            } else {
                return false;
            }
        }else {
            if ($type == "checked") {
                return false;
            }elseif ($type == 'entity'){
                return $returnData;
            }else {
                return true;
            }
        }
    }

    /**
     * 用户忘记密码
     *2016年5月28日 下午3:48:14
     * @param string $email
     */
    public function actionGetpassword(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $email = \YII::$app->request->post('email','');
        if (myhelper::grepcheck($email)){
            $checkUser = $this->actionCheckuser($email, 'entity');
            if ($checkUser['data'] == 1) {
                $to = $email;
                $subject = "Password Reset";
                $code = myhelper::get_resetpwd_code($email);

                if(!$code){
                    $ajax_data['error'] = 2;
                    $ajax_data['msg'] = "User does not exist.";
                    return $ajax_data;
                }

                $callback_url = PROTOCOL.'://'.$_SERVER['HTTP_HOST'].'?email='.$email.'&code='.$code;
                $body = 'Dear '.$email . ',<br /><br />
                            You are receiving this email because you requested a new password for your LoveCrunch account.
                            Please click the link below to reset your password.<br /><br />
                            <a href="' . $callback_url . '">' . $callback_url . '</a><br /><br />
                            If you have received this message in error, please disregard it.
                            <br /><br />
                            The Bycouturier Team';
                $send_result = myhelper::sendEmail($subject,$to,$body);
                if ($send_result) {
                    $ajax_data['error'] = 0;
                    $ajax_data['get_password_code'] = $code;
                    $this->sessions['get_password_code'] = $code;
                    $ajax_data['msg'] = "Password recovery instructions have been sent to your email.";
                    return $ajax_data;
                } else {
                    $ajax_data['error'] = 1;
                    $ajax_data['msg'] = 'unknown error';
                    return $ajax_data;
                }
            } else {
                $ajax_data['error'] = 2;
                $ajax_data['msg'] = "User does not exist.";
                return $ajax_data;
            }
        }else {
            $ajax_data['error'] = 2;
            $ajax_data['msg'] = "User does not exist.";
            return $ajax_data;
        }
    }
    /**
     * 用户重设密码
     *2016年5月28日 下午5:08:16
     */
    public function actionRepassword() {
        if (\Yii::$app->request->isPost) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $password = \Yii::$app->request->post('password', '');
            $email = \Yii::$app->request->post('email', '');
            $code = \Yii::$app->request->post('code', '');
            $verify = myhelper::verify_resetpwd_code($email,$code);
            if(!$verify){
                $ajax_data['error'] = 1;
                $ajax_data['msg'] = "Please request another password recovery email.";
                return $ajax_data;
            }

            $res = $this->resetpwd($email, $password);//通过接口修改用户密码
            if ($res === false) {
                $ajax_data['error'] = 1;
                $ajax_data['msg'] = "Please request another password recovery email.";
                return $ajax_data;
            } else {
                $this->sessions['get_password_id'] = null;
                $ajax_data['error'] = 0;
                $ajax_data['msg'] = "Password successfully reset.";
                return $ajax_data;
            }
        } else {
            return $this->redirect(['/404']);
        }
    }

    /**
     * 通过接口重置密码
     *2016年6月1日 上午10:08:33
     * @param string $email
     * @param string $password
     */
    private function resetapi($email = '', $password = ''){
        $data = false;
        if ($email && $password){
            $key = \YII::$app->params['TOKEN']['ucentkey'];
            $ucenter = \YII::$app->params['MY_URL']['UCENTER'];
            $url = $ucenter.'/api/resetpw';

            $sign = md5('00001'.$email.$password.$key);
            $params = array(
                'email'=>$email,
                'password'=>$password,
                'sid' => '00001',
                'sign' => $sign,
            );
            $returnData = myhelper::http($url, $params, 'POST');
            if ($returnData){
                $returnArr = json_decode($returnData, true);
                $state = $returnArr['state'];
                if ($state == 0)
                    $data = true;
            }
        }
        return $data;
    }

    private function resetpwd($email = '', $password = ''){
        $data = false;
        if ($email && $password){
            $key = \YII::$app->params['TOKEN']['ucentkey'];
            $ucenter = \YII::$app->params['MY_URL']['UCENTER'];
            $url = $ucenter.'/api/reset-password';

            $post = $params = array(
                'timestamp'=>time(),
                'email'=>$email,
                'password'=>$password,
                'sid' => '00001',
            );
            sort($params, SORT_STRING);
            $sign = md5($key . implode("", $params));
            $post['signature'] = $sign;
            $returnData = myhelper::http($url, $post, 'POST');

            if ($returnData){
                $state = $returnData['state'];
                if ($state == 0)
                    $data = true;
            }
        }
        return $data;
    }


    public function actionCheckpwd(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return true;
        $email =\Yii::$app->request->post('email','');
        $password =\Yii::$app->request->post('password','');
        $returnData = $this->ucenter->Login()->loginGW(trim($email), $password);
        if( !$returnData ){
            $this->result['code'] = 1028;
            return false;
        }
        if( $returnData['code'] ){
            $this->result['code'] = 1029;
            $this->result['data'] = $returnData;
            return false;
        }
        $data = $returnData['data'];

        if ($data){
            return true;
        }else {
            return false;
        }
    }


    private function createcookie($ucenter){
        //cookie保存时长
        $expire =  60 * 60 * 24;//1 day

        $user_ip = myhelper::get_client_ip();
        $Ip = new IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
        $region_result = $Ip->getlocation($user_ip); // 获取某个IP地址所在的位置

        if (!empty($region_result['country'])) {
            $where['name_zh'] = $region_result['country'];
        } else {
            $where['name_zh'] = "美国";
        }

        $regionService = new Region();
        $user_region = $regionService->search($where);
        if (!empty($user_region)) {
            $where['name_zh'] = "美国";
            $user_region = $regionService->search($where);
        }

        $user_region['ip'] = $user_ip;

        $auth = array(
            'uid' => $ucenter['uid'],
            'username' => !empty($ucenter['username'])?$ucenter['username']:$ucenter['account'],
            'thumb_avatar'=>$ucenter['avatar'],
            'email' => $ucenter['account'],
            'last_login_time' => isset($ucenter['lasttime'])?$ucenter['lasttime']:time(),
        );

        $this->cookies_2->add(new \yii\web\Cookie([
            'name' => 'user_region',
            'value' => $user_region,
            'expire'=>time()+$expire,
            'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
        ]));
        $this->cookies_2->add(new \yii\web\Cookie([
            'name' => 'user_auth',
            'value' => $auth,
            'expire'=>time()+$expire,
            'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
        ]));
        $this->cookies_2->add(new \yii\web\Cookie([
            'name' => 'user_auth_sign',
            'value' => myhelper::data_auth_sign($auth),
            'expire'=>time()+$expire,
            'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
        ]));
    }


    /**
     * @param $token ：注册后返回的token
     * set国家:对应数据库的ID
     */
    private function setCountry($token){
        //得到国家对应数据库的ID
        $country_name = myhelper::getLocationInfoByIp();
        $regionService = new Region();
        $params = ['region_name' => $country_name];
        $area_code = $regionService->search($params);
        if(!empty($area_code) && $area_code['id']){
            (new \Ucenter\User(['env'=>ENV,'domain'=>DOMAIN]))->updateuser($token, array(
                'country'  => $area_code['id'],
            ));
        }
    }


}
