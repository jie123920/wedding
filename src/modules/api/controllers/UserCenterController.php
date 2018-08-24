<?php
/**
 * Created by IntelliJ IDEA.
 * User: shihuipeng
 * Date: 2017/6/13
 * Time: 上午11:05
 */

namespace app\modules\api\controllers;
use app\modules\api\services\Region;
use Ucenter\Ucenter;
use Yii;
use app\modules\wedding\services\UserNew;
class UserCenterController extends CommonController
{
    public function init()
    {
        parent::init();
        parent::behaviors();
    }

    public function actionLogin()
    {
        $request = YII::$app->request->get();
//        $uid = YII::$app->request->get();
//        list($uid,$game_id,$token,$platform) = array_values($request);

        $ucenter = new Ucenter($request);
        list($code,$data) = $ucenter->LoginWithConnect();
        if($code == 0 ){//把用户中主Token保存cookie中
            return $this->result($code, $data, 'success');
        } else {
            return $this->result($code, [], $data);
        }
    }


    //获取用户中心的国家列表
    public function actionRegion(){
        $regionService = new Region();
        $region_list = $regionService->countries();

        return $this->result(0, $region_list, 'ok');
    }

    //更新用户基本信息
    public function actionUpdate()
    {
        $result = (new \Ucenter\User(['env'=>ENV]))->updateuser(null, array(
            'username' => \Yii::$app->request->post('username'),
            'gender'   => (int) \Yii::$app->request->post('gender'),
            'birth'    => \Yii::$app->request->post('birth'),
            'country'  => \Yii::$app->request->post('country'),
            'mobile'   => \Yii::$app->request->post('mobile'),
            'skype'    => \Yii::$app->request->post('skype'),
        ));
        if (isset($result['code']) && $result['code'] == 0) {
            $this->cookies_2->add(new \yii\web\Cookie([
                'name' => 'last_update_time',
                'value' => time(),
                'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
            ]));
            $this->sessions['user_data'] = null;
                $user_info = $this->getCookieUser();
                $uid = 0;
                if(isset($user_info['id']))
                $uid = $user_info['id'];
               if($uid){
                    $new_user_data = [
                    'uid'      => $uid,
                    'username' => \Yii::$app->request->post('username'),
                    'gender'   => (int) \Yii::$app->request->post('gender'),
                    'birth'    => \Yii::$app->request->post('birth'),
                    'country'  => \Yii::$app->request->post('country'),
                    'mobile'   => \Yii::$app->request->post('mobile'),
                    'skype'    => \Yii::$app->request->post('skype'),
                     
                    ];
                   $res = (new UserNew())->update($new_user_data);
                 }
        } else {
            if (isset($result['error'])) {
                return $this->result(1, [],$result['error']);
            }
        }
        return $this->result(0, [], \YII::t('common','ChangeSuccessful'));
    }

    //更改密码
    public function actionUpdatePwd()
    {
        $result = (new \Ucenter\User(['env'=>ENV]))->updatepwd(null, \Yii::$app->request->post('oldpassword'), \Yii::$app->request->post('password'));
        if (isset($result['code']) && $result['code'] == 0) {
            $this->cookies_2->add(new \yii\web\Cookie([
                'name' => 'last_update_time',
                'value' => time(),
                'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
            ]));
            $this->sessions['user_data'] = null;
            return $this->result(0, [], \YII::t('common','ChangeSuccessful'));
        }
        if (isset($result['code']) && $result['code'] == 1003) {
            return $this->result(1003, [],'old password is error');
        }
        return $this->result(1, [],'unknow error!');
    }




    public function actionUser(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $user_info = $this->getCookieUser();
        if(!$user_info){
            return $this->result(1, [],'unknow error!');
        }
        return $this->result(0, $user_info,'ok!');
    }

}
