<?php
namespace app\modules\wedding\controllers;
use app\modules\wedding\services\Region;
use yii\web\Controller;
use app\helpers\myhelper;
use app\modules\wedding\services\Goods;
use app\modules\wedding\services\Cart;
use app\modules\shop\models\GoodsCart;

class CommonController extends Controller {
    public $enableCsrfValidation = false;//CSRF
    public $view = NULL;
    public $cookies = NULL;//read
    public $cookies_2 = NULL;//write
    public $sessions = NULL;
    public $layout = '';
    public $ucenter = null;
    public $user_info = [];
    public $is_login = 0;
    public $uid = 0;
    public $game_list = [];
    public function init(){
        $this->layout = '@module/views/'.GULP.'/public/main-shop.html';
        $this->ucenter = new \Ucenter\User(['env'=>ENV,'domain'=>DOMAIN]);
        $this->view = \Yii::$app->view;
        $this->cookies = \Yii::$app->request->cookies;
        $this->cookies_2 = \Yii::$app->response->cookies;
        $this->sessions = \Yii::$app->session;
        $this->game_list = [
            [
                'id'=>7,
                'game_name'=>'ClothesForever'
            ],
        ];
        $this->view->params['is_login']= 0;
        $this->view->params['login_show']= 0;
        $this->view->params['user_info']= [];
        $this->view->params['ticket_count']= 0;
        $this->view->params['system_msg_count']= 0;
        $this->view->params['cart_num']= 0;

        //广告来源写入COOKIE 保留60天
        $ads = \yii::$app->request->get("utm_source","");
        if(in_array($ads,['GG','FB'])){
            $this->cookies_2->add(new \yii\web\Cookie([
                'name' => 'ads',
                'value' => \yii::$app->request->getHostInfo().\yii::$app->request->url,
                'expire'=>time()+60*3600*24,
                'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
            ]));
        }

        $user_info = $this->getCookieUser();//判断用户是否登录
        $this->view->params['cart_num']  = Cart::CartNum(0,\YII::$app->request->cookies);//COOKIE CART
        if ($user_info) {
            $this->view->params['is_login'] = $this->is_login = 1;
            $this->view->params['user_info'] = $this->user_info = $user_info;
            $this->view->params['cart_num'] = $cart_num = Cart::CartNum($user_info['id'],\YII::$app->request->cookies);
            $this->uid = $user_info['id'];
            $temp_uid = 0;
            if($this->cookies->has('temp_uid'))
            $temp_uid = $this->cookies->get('temp_uid','')->value;
            GoodsCart::init_cookie_cart($user_info['id'],$temp_uid);

            $this->view->params['message_count']    = $user_info['message_count'];
            $this->view->params['ticket_count']     = $user_info['ticket_count'];
            $this->view->params['system_msg_count'] = $user_info['system_msg_count'];
        }else{
            //wdx 0723
              if(!$this->cookies->has('temp_uid')){
                 $time = 'guest_'.time().rand(1000,9999);
                 $this->cookies_2->add(new \yii\web\Cookie([
                    'name' => 'temp_uid',
                    'value' => $time,
                    'expire'=>time()+60*3600*24,
                    'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
                ]));
               }
              if(!$this->cookies->has('http_referer')){
                if(isset($_SERVER['HTTP_REFERER']))
                 $this->cookies_2->add(new \yii\web\Cookie([
                    'name' => 'http_referer',
                    'value' => $_SERVER['HTTP_REFERER'],
                    'expire'=>time()+60*3600*24,
                    'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
                ]));
              }
              if(!$this->cookies->has('adv_key')){
                   $adv_key = \yii::$app->request->get('utm_source','');
                   if(empty($adv_key))
                    $ads = [];
                    if($this->cookies->has("ads"))
                    $ads = $this->cookies->get("ads","");
                    if($ads){
                        $tempres= explode('&',$ads);
                        if($tempres){
                            foreach ($tempres as $k => $val) {
                                if($val)
                                if(strpos($val,'utm_source') !==false){
                                    $temparr = explode('=',$val);
                                    if(isset($temparr[1])){
                                        $adv_key  = $temparr[1];
                                    } 
                                }
                            }
                        }
                    }
                if($adv_key){
                     $this->cookies_2->add(new \yii\web\Cookie([
                        'name' => 'adv_key',
                        'value' => $adv_key,
                        'expire'=>time()+60*3600*24,
                        'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
                     ]));
                }
              }
              // var_dump($temp_uid);
              // var_dump($this->cookies->get('http_referer'));
           
        }
        $this->getexChangeRate();

        $this->view->params['category'] = Goods::Categories(SHOP_ID);
        foreach ($this->view->params['category'] as $k=>$data){//排除便宜货分类
            if($data['id'] == \yii::$app->params['low_category_id']){
                if(!in_array(REGION_ID,\yii::$app->params['low_country'])){
                    unset($this->view->params['category'][$k]);
                }
            }
        }

        $this->view->params['header_block'] = Goods::multi_get_block_data([BLOCK_1],3,LANG_SET);
        // 默认 SEO 信息
        $this->view->params['meta_title']= \Yii::t('shop', 'Couture Wedding Dresses for Bridals, Bridemaids & More | Bycouturier');
        $this->view->params['description']= \Yii::t('shop', 'Shop our selection of stunning wedding dresses and gowns, bridesmaid dresses, and mother of the bride dresses. Unique styles & latest trends available.');
        $this->view->params['keyword']= \Yii::t('shop', 'Wedding Dress');

        $this->view->params['tlang'] = '';
        $this->view->params['think_language'] = '';
        $this->view->params['jslang'] = '';
         //var_dump($_SERVER['REQUEST_URI']); 
        if(isset($_COOKIE['think_language']) && $_COOKIE['think_language'] == 'de-de'){ //wdx 0621
            $this->view->params['jslang'] = $_COOKIE['think_language'];
            $this->view->params['think_language'] =  $_COOKIE['think_language'];
            $thinklang = explode('-',$_COOKIE['think_language']);
            if(isset($thinklang[0]))
            $this->view->params['tlang'] =  '/'.$thinklang[0];
            $tlang = $this->view->params['tlang'];
            if($tlang){
                $current_url = \yii::$app->params['MY_URL']['BS'].$_SERVER['REQUEST_URI']; 
                if($_SERVER['REQUEST_URI']){
                  $tu =   explode('de/',$_SERVER['REQUEST_URI']);
                  if(!isset($tu[1])){
                     $current_url = \yii::$app->params['MY_URL']['BS'].'/de'.$_SERVER['REQUEST_URI'];
                  }
                }
                $temp = '';
                if($_SERVER['REQUEST_URI']){
                     $temp = str_replace('/de/','/',$_SERVER['REQUEST_URI']);
                }
                $current_default_url = \yii::$app->params['MY_URL']['BS'].$temp;

                $alternate =  \yii::$app->params['MY_URL']['BS']."/".$tlang;
                $bs = \yii::$app->params['MY_URL']['BS'];
                $alternate =  '<link rel="alternate"  hreflang="'.$thinklang[0].'" href="'.$current_url.'">'.'<link rel="alternate" hreflang="x-default" href="'.$current_default_url.'">';
                $this->view->params['alternate'] = $alternate;
            }
          
        }else{
            $current_url = \yii::$app->params['MY_URL']['BS'].$_SERVER['REQUEST_URI'];
            $current_de_url = \yii::$app->params['MY_URL']['BS'].'/de'.$_SERVER['REQUEST_URI'];
            $alternate =  '<link rel="alternate"  hreflang="de" href="'.$current_de_url.'">'.'<link rel="alternate" hreflang="x-default" href="'.$current_url.'">';
                $this->view->params['alternate'] = $alternate;
        }
        
        $this->view->params['bs'] = \yii::$app->params['MY_URL']['BS'].$this->view->params['tlang'];
        $this->view->params['bread'] = [];
    }

    /**
     * 内部用户退出登录
     */
    public  function logout() {
        $token = $this->sessions['userinfo']['token'];
        if (isset($token)){
            (new \Ucenter\Ucenter(['domain'=>DOMAIN, 'env'=>ENVUC]))->User()->logout($token);
        }
        $this->sessions['userinfo'] = null;
        $this->cookies_2->removeAll();
    }

    /**
     * 检测用户是否登录
     * @return integer 0-未登录，大于0-当前登录用户ID
     */
    function is_login()
    {
        if($this->ucenter->getToken()){
            return true;
        }else {
            if ($this->ucenter->getEncodeCookie('remember_me_token')) {
                return true;
            }
            return 0;
        }
    }

    public function actionEmpty() {
        // header(" HTTP/1.1  404  Not Found");
        header("status: 404 Not Found");
        return $this->render('/public/404.html', [
            'login_show'=>1,'bsurl'=>\yii::$app->params['MY_URL']['BS']
        ]);
        \YII::$app->end();
    }

    public function actionMaintenance() {
        return $this->render('/public/maintenance.html', [
        ]);
        \YII::$app->end();
    }

    //类似于无限极分类的做法，通过子类找父类
    //面包屑导航
    public function getParents ($list, $id) {
        $arr = array();
        foreach ($list as $v) {
            if ($v['id'] == $id) {
                $arr[] = $v;
                $arr = array_merge($this->getParents($list, $v['parent_id']),$arr);
            }
        }
        return $arr;
    }

    protected function getCookieUser()
    {
        if(!isset($_COOKIE['_ttl']) && !isset($_COOKIE['remember_me_token'])){
            return false;
        }
        $userData = $ttl = null;
        if (isset($this->sessions['user_data'])) {
            $userData = $this->sessions['user_data'];
        }
        if (isset($_COOKIE['_ttl'])) {//只存在1天
            $ttl = $_COOKIE['_ttl'];
        }

        if ($userData && $ttl == $userData['ttl']) {
            return $userData;
        }

        $userData = $this->ucenter->userinfo(null, 'id,email,country,username,avatar,gender,birth,skype,mobile,platform');

        if(($userData && $userData['code']!=0) || !$userData){
            $remember_me_token = $this->ucenter->getEncodeCookie('remember_me_token');
            if($remember_me_token){
                $userData = $this->autoLogin($remember_me_token);
                if($userData && $userData['code'] == 0){
                    $userData = $this->ucenter->userinfo($userData['data']['token'], 'id,email,country,username,avatar,gender,birth,skype,mobile,platform');
                    if($userData && $userData['code']!=0){//未获取接口 退出
                        return false;
                    }
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }
        $userData               = $userData['data'];
        $userData['avatar_url'] = $userData['avatar'];
        $userData['birth_data'] = strtotime($userData['birth']);
        $userData['ttl']        = $ttl;

        $data = myhelper::sendRequest(['uid'=>$userData['id'],'game_id'=>7],'GET',true,\Yii::$app->params['MY_URL']['MUTANTBOX'].'/api/ticket/get-count');
        $userData['message_count']= 0;
        $userData['ticket_count']= 0;
        $userData['system_msg_count']= 0;
        if($data && isset($data['code'])){
            if ($data['code']==0){
                $userData['message_count']= isset($data['data']['message_count'])?$data['data']['message_count']:0;
                $userData['ticket_count']= isset($data['data']['ticket_count'])?$data['data']['ticket_count']:0;
                $userData['system_msg_count']= isset($data['data']['system_msg_count'])?$data['data']['system_msg_count']:0;
            }
        }


        setcookie('user',json_encode($userData),time()+3600*365,'/',DOMAIN);
        $this->sessions['user_data']  = $userData;

        return $userData;
    }

    public function isLogin() {
        if (!($this->is_login)) {
            return $this->redirect(['/login']);\YII::$app->end();
//            $this->view->params['login_show']= 1;
//            return $this->render('/public/404.html', [
//            ]);
//            \YII::$app->end();
        }
    }


    /**
     * 记住我功能
     * @param string $remember_me_token
     */
    public function autoLogin($remember_me_token='') {
        $returnData = (new \Ucenter\Ucenter(['domain'=>DOMAIN, 'env'=>ENVUC]))->User()->autoLogin($remember_me_token);
        if($returnData && $returnData['code']==0){
            $data = $returnData['data'];
            $this->sessions['userinfo'] = $data;
            $this->sessions['user_data'] = null;

            $expire =  60 * 60 * 24;//1 day
            $auth = array(
                'uid' => $data['uid'],
                'username' => !empty($data['username'])?$data['username']:$data['account'],
                'thumb_avatar'=>$data['avatar'],
                'email' => $data['account'],
                'last_login_time' => isset($data['lasttime'])?$data['lasttime']:time(),
            );

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

            $this->cookies_2->add(new \yii\web\Cookie([
                'name' => 'last_update_time',
                'value' => time(),
                'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
            ]));
        }
        return $returnData;
    }


    public function error($title){
        $this->layout = '@module/views/'.GULP.'/public/main.html';
        return $this->render('/public/404.html', [
            'title'=>$title,
        ]);
    }


    /**
     * 定义汇率常量
     * 2017年4月27日 上午10:44:19
     * @author liyee
     */
    private function getexChangeRate() {

        $default = [
            'name'=>'USD',
            'm'=>'1.000000',
            'symbol'=>'US$',
            'region_name'=>'United States',
            'country_id'=>'235'
        ];

        if(\yii::$app->request->get('rid')){//url传参优先读取
            $id = \yii::$app->request->get('rid', '');
            $list = Region::GetIdByIp($id,myhelper::get_client_ip());
            $default = isset($list['default'])?$list['default'] : $default;

            $name = $default['name'];
            $m = $default['m'];
            $symbol = $default['symbol'];
            $region_name = $default['region_name'];
            $country_id = $default['country_id'];

            setcookie('think_rate_id',$default['id'],time()+3600,'/',DOMAIN);
            setcookie('think_rate_name',$name,time()+3600,'/',DOMAIN);
            setcookie('think_rate_m',$m,time()+3600,'/',DOMAIN);
            setcookie('think_rate_symbol',$symbol,time()+3600,'/',DOMAIN);
            setcookie('think_rate_region_name',$region_name,time()+3600,'/',DOMAIN);
            setcookie('country_id',$country_id,time()+3600,'/',DOMAIN);
        }else{
            $id = isset($_COOKIE['think_rate_id'])?$_COOKIE['think_rate_id']:'';
            $list = Region::GetIdByIp($id,myhelper::get_client_ip());
            $default = isset($list['default'])?$list['default'] : $default;

            $name = isset($_COOKIE['think_rate_name'])?$_COOKIE['think_rate_name']:$default['name'];
            $m = isset($_COOKIE['think_rate_m'])?$_COOKIE['think_rate_m']:$default['m'];
            $symbol = isset($_COOKIE['think_rate_symbol'])?$_COOKIE['think_rate_symbol']:$default['symbol'];
            $region_name = isset($_COOKIE['think_rate_region_name'])?$_COOKIE['think_rate_region_name']:$default['region_name'];
            $region_id = isset($_COOKIE['country_id'])?$_COOKIE['country_id']:$default['country_id'];
        }

        defined('THINK_RATE_NAME') or define('THINK_RATE_NAME', $name);
        defined('THINK_RATE_M') or define('THINK_RATE_M', $m);
        defined('THINK_RATE_SYMBOL') or define('THINK_RATE_SYMBOL', $symbol);
        defined('THINK_RATE_REGION_NAME') or define('THINK_RATE_REGION_NAME', $region_name);
        defined('REGION_ID') or define('REGION_ID', $region_id);
    }

    /**
     * 定义json返回结果统一格式
     * 2017年5月24日 下午2:44:42
     * @author liyee
     * @param number $code
     * @param string $message
     * @param unknown $data
     */
    protected function result($code = 0, $message = '', $data = []) {
        return [
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * 检测登录
     * @param string $referer
     * @param bool $isAjax
     * @return array
     */
    public function check_user($referer='/',$isAjax=false){
        if(!$this->is_login){
            if($isAjax){
                echo json_encode([ 'code' => -1, 'message' => 'please log in', 'data'    => '' ]) ;exit;
            }else{
                $this->redirect(['/login?referer='.$referer]);\YII::$app->end();
            }
        }
    }

}
