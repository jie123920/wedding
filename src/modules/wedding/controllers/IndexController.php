<?php
namespace app\modules\wedding\controllers;
use \app\helpers\myhelper;
use app\modules\wedding\services\Goods;
use app\modules\shop\models\ShopCountryExchange;
use app\modules\shop\models\Region;
use app\Library\Mobile_Detect;
class IndexController extends CommonController
{
    public function init()
    {
        parent::init();
        $this->layout = '@module/views/'.GULP.'/public/main-shop.html';
        $this->view->params['active_index'] = '1';
    }

    public function actionIndex()
    {

        //跳到手机版
        if((new Mobile_Detect())->isMobile()){
            $Murl = \yii::$app->params['MY_URL']['M'];
            if(\yii::$app->request->queryString){
                $Murl .= "?".\yii::$app->request->queryString;
            }
            $this->redirect($Murl);
        }



        $code = \YII::$app->request->get('code','');
        $email = \YII::$app->request->get('email','');
        $get_password_code  = false;
        if (!empty($code) && !empty($email)) {
            $get_password_code = true;
            $verify = myhelper::verify_resetpwd_code($email,$code);
            if (!$verify) {
                $get_password_js = '<script>showDialog("#reset_pwd")</script>';
                $this->view->params['get_password_js']= $get_password_js;
            } else {
                $get_password_js = '<script>layer_alert("Please request another password recovery email.",1,"/");</script>';
                $this->view->params['get_password_js']= $get_password_js;
            }
        }

        $this->view->params['get_password_code']= $get_password_code;
        $this->view->params['email']= $email;
        $data = Goods::multi_get_block_data([BLOCK_1,BLOCK_2,BLOCK_3,BLOCK_4,BLOCK_5,BLOCK_6,BLOCK_7],50,LANG_SET);
        return $this->render('index.html', [
            'isLogined'       => $this->is_login,
            'block'=>$data
        ]);
    }


    //点击获取更多国家货币信息
    public function actionGetMoreCountry() {
        if(\Yii::$app->request->isAjax){
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            $list = ShopCountryExchange::listInfo();

            $coungtries = Region::countriesNew();

            $show_data = Region::get_country_data(10, true);

            $result = myhelper::get_country_currency_list($show_data, $coungtries, $list);

           $result = array_merge($result);

            //拼接返回客户端
            return array('ok' => 0, 'data' => $result);
        }
    }
}
