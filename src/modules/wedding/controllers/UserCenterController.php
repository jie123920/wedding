<?php
namespace app\modules\wedding\controllers;

use app\Library\ShopPay\ShopPay;
use app\modules\wedding\models\Message;
use app\Library\gameapi\Play;
use app\modules\wedding\services\Region;
use app\modules\wedding\services\Goods;
use Yii;
use yii\data\Pagination;
use app\modules\wedding\services\UserNew;
class UserCenterController extends CommonController
{
    public $defaultAction = 'index';
    public function init()
    {
        parent::init();
        $this->layout = '@module/views/'.GULP.'/public/user.html';
        $this->check_user(\Yii::$app->request->getAbsoluteUrl());
        $user_menu = [];
        $user_menu[0] = array("url" => "/user-center/index", "name" => \yii::t("common","GeneralSettings"));
        $user_menu[1] = array("url" => "/user-center/binduser", "name" => \yii::t("common","Connect Accounts"));
        $user_menu[2] = array("url" => "/user-center/updatepwd", "name" => \yii::t("common","UpdatePassword"));
        $user_menu[5] = array("url" => "/user-center/message", "name" => \yii::t("common","System Messages"));
        $user_menu[6] = array("url" => "/user-center/myorder", "name" => \yii::t("shop","MyOrders"));
        $user_menu[7] = array("url" => "/user-center/favorite-goods", "name" => \yii::t("shop","My Favorite Goods"));
        $user_menu[8] = array("url" => "/user-center/address", "name" => \yii::t("shop","Address Book"));
        $result     = $this->ucenter->getbinded(null, $this->user_info['id']);
        if (!isset($result['code']) || $result['code'] != 0) {
            $this->logout();//接口失败 清空所有COOKIE 并退出
            return $this->redirect(['/404']);
        }
        if(!in_array('gw',$result['data'])){
            unset($user_menu[2]);
        }

        $bread = [];
        $bread[] = [
            'url'=>  '/user-center/index',
            'name'=>  \Yii::t('shop','User Center')
        ];
        foreach ($user_menu as $key => $value) {
            if (strip_tags($_SERVER['REQUEST_URI']) == $value['url']) {
                foreach ($user_menu as $_user_menu){
                    if($value['url'] == $_user_menu['url']){
                        $bread[] = [
                            'url'=>  $value['url'],
                            'name'=>  $value['name']
                        ];
                        break;
                    }
                }

                $user_menu[$key]['active'] = "on";
            }else{
                $user_menu[$key]['active'] = "off";
            }
        }

        $this->view->params['bread'] = $bread;
        $this->view->params['user_menu'] = $user_menu;
    }

    public function actionIndex()
    {
        if (\Yii::$app->request->post()) {
            $result = (new \Ucenter\User(['env'=>ENV]))->updateuser(null, array(
                'username' => \Yii::$app->request->post('username'),
                'gender'   => (int) \Yii::$app->request->post('gender'),
                'birth'    => \Yii::$app->request->post('birth_data'),
                'country'  => \Yii::$app->request->post('region_id'),
                'mobile'   => \Yii::$app->request->post('mobile'),
                'skype'    => \Yii::$app->request->post('skype'),
            ));
            $ajax_data = array('error' => 1, 'msg' => 'Unkonw Error');
            if (isset($result['code']) && $result['code'] == 0) {
                $ajax_data['error'] = 0;
                $ajax_data['msg']   = \YII::t('common','ChangeSuccessful');
                $this->cookies_2->add(new \yii\web\Cookie([
                    'name' => 'last_update_time',
                    'value' => time(),
                    'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
                ]));
                $this->sessions['user_data'] = null;
                if(isset($this->user_info['id'])){
                    $new_user_data = [
                    'uid'      => $this->user_info['id'],
                    'username' => \Yii::$app->request->post('username'),
                    'gender'   => (int) \Yii::$app->request->post('gender'),
                    'birth'    => \Yii::$app->request->post('birth_data'),
                    'country'  => \Yii::$app->request->post('region_id'),
                    'mobile'   => \Yii::$app->request->post('mobile'),
                    'skype'    => \Yii::$app->request->post('skype'),
                     
                    ];
                   $res = (new UserNew())->update($new_user_data);
                 }

            } else {
                if (isset($result['error'])) {
                    $ajax_data['msg'] = $result['error'];
                }
            }
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $ajax_data;
        } else {
            $regionService = new Region();
            $region_list = $regionService->countries();
            return $this->render('index.html', [
                'region_list'=>$region_list,
                'max_age'=>date("Y-m-d", strtotime("-13 year")),
            ]);
        }
    }

    public function actionUpdate()
    {
        if (\Yii::$app->request->post()) {
            $result = (new \Ucenter\User(['env'=>ENV]))->updateuser(null, array(
                'username' => \Yii::$app->request->post('username'),
                'gender'   => (int) \Yii::$app->request->post('gender'),
                'birth'    => \Yii::$app->request->post('birth_data'),
                'country'  => \Yii::$app->request->post('region_id'),
                'mobile'   => \Yii::$app->request->post('mobile'),
                'skype'    => \Yii::$app->request->post('skype'),
            ));
            $ajax_data = array('error' => 1, 'msg' => 'Unkonw Error');
            if (isset($result['code']) && $result['code'] == 0) {
                $ajax_data['error'] = 0;
                $ajax_data['msg']   = \YII::t('common','ChangeSuccessful');
                $this->cookies_2->add(new \yii\web\Cookie([
                    'name' => 'last_update_time',
                    'value' => time(),
                    'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
                ]));
                $this->sessions['user_data'] = null;
            } else {
                if (isset($result['error'])) {
                    $ajax_data['msg'] = $result['error'];
                }
            }
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $ajax_data;
        } else {
            $regionService = new Region();
            $region_list = $regionService->countries();
            return $this->render('update.html', [
                'region_list'=>$region_list,
                'max_age'=>date("Y-m-d", strtotime("-13 year")),
            ]);
        }
    }


    public function actionBinduser()
    {
        $user_info = $this->user_info;
        $userCenter = new \Ucenter\User(['env'=>ENV]);
        $result     = $userCenter->getbinded(null, $user_info['id']);

        if (!isset($result['code']) || $result['code'] != 0) {
            return $this->redirect(['/404']);
        }
        return $this->render('bindUser.html', [
            'account_list'=>$result['data'],
        ]);
    }

    public function actionUpdatepwd()
    {
        $user_info = $this->user_info;
        if (\Yii::$app->request->post()) {
            $result = (new \Ucenter\User(['env'=>ENV]))->updatepwd(null, \Yii::$app->request->post('oldpassword'), \Yii::$app->request->post('password'));

            $ajax_data = array('error' => 1, 'msg' => 'Unkonw Error');
            if (isset($result['code']) && $result['code'] == 0) {
                $ajax_data['error'] = 0;
                $ajax_data['msg']   = \YII::t('common','ChangeSuccessful');
            } else {
                if (isset($result['code']) && $result['code'] == 1027) {
                     $ajax_data['msg'] = 'old password is error';
                }
            }
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $ajax_data;
        } else {

            $userCenter = new \Ucenter\User(['env'=>ENV]);
            $result     = $userCenter->getbinded(null, $user_info['id']);

            if (!isset($result['code']) || $result['code'] != 0) {
                return $this->redirect(['/404']);
            }

            return $this->render('updatePwd.html', [
                'show_oldpassword'=>0,
            ]);
        }
    }

    public function actionOrderlist()
    {
        $user_info    = $this->user_info;
        $page         = 1; //当前页
        $perpage      = 6; //每次显示条数
        $start = ($page - 1) * $perpage;
        $uid          = '\'fb_' . $user_info['id'] . '\',' . '\'gw_' . $user_info['id'].'\','.'\''.$user_info['id'].'\'';
        $payOrderInfo = \Yii::$app->dbpay->createCommand("select * from pay_orders where uid IN($uid) and status !=0 and gameid=7 and source<>'shop' order by createtime DESC limit $start, $perpage")->queryAll();
        if (!empty($payOrderInfo)) {
            foreach ($payOrderInfo as $key => $value) {
                $packid = $value['pack_id'];
                $mealInfo = \Yii::$app->dbpay->createCommand("select * from pay_platform_currency where id=$packid")->queryAll();
                $name = isset($mealInfo[0]['name'])?$mealInfo[0]['name']:'';
                $payOrderInfo[$key]['mealName'] = $name;
                $payOrderInfo[$key]['serverName'] = Play::getserverinfo($value['serverid']);
                if ($payOrderInfo[$key]['currency'] == 'US') {
                    $payOrderInfo[$key]['currency'] = 'USD';
                }
                $payOrderInfo[$key]['price'] = $payOrderInfo[$key]['currency'] . ' ' . $payOrderInfo[$key]['amount'];
            }
        }
        return $this->render('orderList.html', [
            'payOrderInfo'=>$payOrderInfo,
        ]);
    }

    public function actionMoreorder()
    {
        if (\Yii::$app->request->isAjax) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $user_info    = $this->user_info;
            $page         = \Yii::$app->request->post('page'); //当前页
            $perpage      = 6; //每次显示条数
            $start = ($page - 1) * $perpage;
            $uid          = '\'fb_' . $user_info['id'] . '\',' . '\'gw_' . $user_info['id'].'\','.'\''.$user_info['id'].'\'';
            $payOrderInfo = \Yii::$app->dbpay->createCommand("select * from pay_orders where uid IN($uid) and status !=0  and gameid=7 and source<>'shop' order by createtime DESC limit $start, $perpage")->queryAll();
            $str          = '';
            if (!empty($payOrderInfo)) {
                foreach ($payOrderInfo as $key => $value) {
                    $packid = $value['pack_id'];
                    $mealInfo = \Yii::$app->dbpay->createCommand("select * from pay_platform_currency where id=$packid")->queryAll();
                    $name = isset($mealInfo[0]['name'])?$mealInfo[0]['name']:'';
                    $payOrderInfo[$key]['serverName'] = Play::getserverinfo($value['serverid']);
                    $payOrderInfo[$key]['mealName'] = $name;
                    if ($payOrderInfo[$key]['currency'] == 'US') {
                        $payOrderInfo[$key]['currency'] = 'USD';
                    }
                    $payOrderInfo[$key]['price'] = $payOrderInfo[$key]['currency'] . ' ' . $payOrderInfo[$key]['amount'];
                    $status                      = $value['status'] == 1 ? 'Completed' : 'Unfinished';
                    $str .= '<tr class="tr_item tr_item2">'
                    . '<td class="item item01">'
                    . '<h2>Date & Time</h2>'
                    . '<p>' . date("M d, Y", $value['createtime']) . '<br />' . date("H:i:s", $value['createtime']) . '</p>'
                        . '</td>'
                        . '<td class="item item02">'
                        . '<h2>Game</h2>'
                        . '<p>Liberators</p>'
                        . '</td>'
                        . '<td class="item item03">'
                        . '<h2>Server</h2>'
                        . '<p>S' . $value['serverid'] . '</p>'
                        . '</td>'
                        . '<td class="item item04">'
                        . '<h2>Pack</h2>'
                        . '<p>' . $payOrderInfo[$key]['mealName'] . '</p>'
                        . '</td>'
                        . '<td class="item item05">'
                        . '<h2>Price</h2>'
                        . '<p>' . $payOrderInfo[$key]['price'] . '</p>'
                        . '</td>'
                        . '<td class="item item06">'
                        . '<h2>Order ID</h2>'
                        . '<p>' . $value['orderid'] . '</p>'
                        . '</td>'
                        . '<td class="item item07">'
                        . '<h2>Status</h2>'
                        . '<p>' . $status . '</p>'
                        . '</td>'
                        . '</tr>';
                }
            }
            return array('ap_str' => $str);
        }
    }


    public function actionReadmore()
    {
        $where['id'] = \Yii::$app->request->post('msg_id');
        $content = (new \yii\db\Query())
            ->select('content')
            ->from('ww2_message')
            ->where($where)
            ->one();

        $result_data['content']   = htmlspecialchars_decode($content['content']);
        $UserMessage              = new Message;
        $uid                      = $this->user_info['id'];
        $data['is_read']          = 1;
        $UserMessage->updateAll($data,'message_id=:message_id',[':message_id'=>\Yii::$app->request->post("msg_id")]);
        $count_where['uid']           = $uid;
        $count_where['is_read']       = 0;
        $result_data['message_count'] = (new \yii\db\Query())
            ->select('id')
            ->from($UserMessage::tableName())
            ->where($count_where)
            ->count();
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $result_data;
    }

    public function actionMessage()
    {
        $uid       = $this->user_info['id'];
        $where['uid'] = $uid;
        $where['game_id'] = 7;
        //每页记录
        $data['page']       = \Yii::$app->request->get('p');
        $data['page_count'] = 4;
        if (\Yii::$app->request->isAjax) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $list = $this->page($where, $data, "ajax");
            return $list;
        } else {
            $list = $this->page($where, $data);
            return $this->render('message.html', [
                'message_list'=>$list,
            ]);
        }
    }

    /* 获取数据类 */

    public function page($where = array(), $data = array(), $type = "")
    {
        return [];
        $list = Message::messageList($where, 'id DESC',($data['page']-1)*$data['page_count'],$data['page_count']);
        if ($type == "ajax") {
            $list = Message::msgAjax($list);
        } else {
            $list = Message::msgFormatting($list);
        }
        return $list;
    }

    public function actionAjaxregioncode()
    {
        $region_id = \Yii::$app->request->post('region_id', 0);
        $regionService = new Region();
        $data = $regionService->one($region_id);
        if (!empty($data)) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['area_code' => $data['area_code']];
        }
    }

    public function actionUploadportrait()
    {
        if ($_FILES['portrait_file']) {
            $upload_config = \Yii::$app->params['ARTICLEPICTURE_UPLOAD'];
            $upload        = new \app\helpers\Image\Upload($upload_config); // 实例化上传类
            $info          = $upload->upload();
            $ajax_data = array("result" => "", "msg" => "", "src" => "", "path" => "", "width" => "", "height" => "");
            if (!$info) {
                $ajax_data['result'] = 1;
                $ajax_data['msg']    = $upload->getError();
                echo '<html>
                            <head>
                            <meta charset="UTF-8"><title></title>
                            <script>document.domain = \''.$_SERVER['HTTP_HOST'].'\'</script>
                            </head>
                            <body>'. json_encode($ajax_data) .'</body>
                            </html>';
            } else {
                $ajax_data['result'] = 0;
                $ajax_data['msg']    = "上传成功";
                $ajax_data['src']    = UPLOAD_IMAGE_FILE_URL. $info['portrait_file']['savepath'] . $info['portrait_file']['savename'];
                $ajax_data['path']   = UPLOAD_IMAGE_FILE . $info['portrait_file']['savepath'] . $info['portrait_file']['savename'];

                $image = new \app\helpers\Image\Image();
                $image->open(UPLOAD_IMAGE_FILE . $info['portrait_file']['savepath'] . $info['portrait_file']['savename']);
                $width  = $image->width(); // 返回图片的宽度
                $height = $image->height(); // 返回图片的高度
                $ajax_data['width']  = $width;
                $ajax_data['height'] = $height;

                echo '<html>
                            <head>
                            <meta charset="UTF-8"><title></title>
                            <script>document.domain = \''.$_SERVER['HTTP_HOST'].'\'</script>
                            </head>
                            <body>'. json_encode($ajax_data) .'</body>
                            </html>';
               // $this->ajaxReturn($ajax_data);
            }
        }
    }

    public function actionCropportrait()
    {

        $crop_img = \Yii::$app->request->post();
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $image = new \app\helpers\Image\Image();

        $image->open($crop_img['src']);
        $save_dir  = dirname($crop_img['src']);
        $save_name = "thumb_" . basename($crop_img['src']);

        $img_info = $image->crop($crop_img['w'], $crop_img['h'], $crop_img['x'], $crop_img['y'], 200, 200)->save($save_dir . '/' . $save_name);
        if (!$img_info) {
            $ajax_data['result'] = 1;
            $ajax_data['msg']    = $image->getError();
            return $ajax_data;
        } else {
            $user_info                = $this->user_info;
            $dirname                  = array_pop(explode("/", $save_dir));

            $data['id']               = $user_info['id'];
            $data['avatar_url']       = $dirname . "/" . $save_name;
            $data['thumb_avatar_url'] = $dirname . "/" . $save_name;

            //图片同步CDN
            $uploadCDNResult = true;
            $upload_path = dirname(UPLOAD_IMAGE_FILE)."/";
            $need_rsync_path = $upload_path.'images/';
            foreach (\YII::$app->params['RSYNC_CDN_ADDRESS'] as  $address) {
                $address = $address.'/images/';
                system("rsync -avr {$need_rsync_path} {$address} > /dev/null", $systemResult);
                if ($systemResult != 0) {
                    $uploadCDNResult = false;
                    break;
                }else{

                }
            }
            if($uploadCDNResult){
                @unlink($upload_path.'images/'.$dirname . "/" . $save_name);//删除缩略图
                @unlink($upload_path.'images/'.$dirname . "/" . basename($crop_img['src']));//删除原图
                $avatar = UPLOAD_CDN_URL.'/images/'.$dirname . "/" . $save_name;
            }else{
                $ajax_data['result'] = 2;
                $ajax_data['msg']    = "Failed to update profile picture.";
                return $ajax_data;
            }

            $updateData = (new \Ucenter\User(['env'=>ENV]))->updateuser(null, array('id'=>$user_info['id'],'avatar'=>$avatar));
            if (isset($updateData['error']) && empty($updateData['error'])) {
                $ajax_data['result'] = 0;
                $ajax_data['msg']    = "Profile picture updated.";

                $this->cookies_2->add(new \yii\web\Cookie([
                    'name' => 'last_update_time',
                    'value' => time(),
                    'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
                ]));
                $this->sessions['user_data'] = null;
                return $ajax_data;
            } else {
                $ajax_data['result'] = 2;
                $ajax_data['msg']    = "Failed to update profile picture.";
                return $ajax_data;
            }
        }
    }

    public function actionAjaxsaveportrait()
    {
        $avater_src = \Yii::$app->request->post('src');
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (!empty($avater_src)) {
            $user_info                = $this->user_info;
            $data['id']               = $user_info['id'];
            $data['avatar_url']       = basename($avater_src);
            $data['thumb_avatar_url'] = basename($avater_src);
            $updateData = (new \Ucenter\User(['env'=>ENV]))->updateuser(null, array('id'=>$user_info['id'],'avatar'=>$avater_src));
            if (isset($updateData['error']) && empty($updateData['error'])) {
                $this->cookies_2->add(new \yii\web\Cookie([
                    'name' => 'last_update_time',
                    'value' => time(),
                    'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
                ]));
                $this->sessions['user_data'] = null;
                $ajax_data['result'] = 0;
                $ajax_data['msg']    = "Profile picture updated.";
                return $ajax_data;
            } else {
                $ajax_data['result'] = 2;
                $ajax_data['msg']    = "Failed to update profile picture.";
                return $ajax_data;
            }
        } else {
            $ajax_data['result'] = 1;
            $ajax_data['msg']    = "Failed to update profile picture.";
            return $ajax_data;
        }
    }


    public function actionMyorder()
    {
        $request = \Yii::$app->request;
        $page     = Yii::$app->request->get('page', 1);
        $pageSize = Yii::$app->request->get('per-page', 10);
        $uid      = $this->user_info['id'];
        
        $shopPay = new ShopPay('bycouturier');
        $returnData = $shopPay->listInfo($uid, $page, $pageSize);
        $orderList = [];
        if (is_array($returnData) && $returnData['code'] == 0) {
            $orderList = $returnData['data']['models'];
        }

        return $this->render('myorder.html', [
            'order_list' => $orderList,
        ]);
    }

    //MORE ORDERS
    public function actionMyorder_more()
    {
        $this->layout = false;
        if(\Yii::$app->request->isAjax){
            $page     = Yii::$app->request->get('page', 1);
            $pageSize = Yii::$app->request->get('per-page', 10);
            $uid      = $this->user_info['id'];

            $shopPay = new ShopPay('bycouturier');
            $returnData = $shopPay->listInfo($uid, $page, $pageSize);
            $orderList = [];
            if ($returnData && $returnData['code'] == 0) {
                $orderList = $returnData['data']['models'];
            }

            return $this->render('_orders.html', [
                'order_list' => $orderList
            ]);
        }
    }

    public function actionFavoriteGoods(){
        $order = YII::$app->request->get('order','');
        $page = YII::$app->request->get('page',1);
        $per_page = YII::$app->request->get('per_page',Goods::PER_PAGE);
        $sort_url = "?page=".$page;
        $list = Goods::MyFavorite($order,LANG_SET,$page,$this->uid,SHOP_ID,$per_page);

        $pages = [];
        if($list){
            $pages = new Pagination(['totalCount' =>$list['total_num'], 'pageSize' =>Goods::PER_PAGE]);
        }

        return $this->render('/people/myfavorite.html', [
            'data'=>$list,
            'pages'=>$pages,
            'sort_url'=>$sort_url,
            'sort'=>$order,
            'keywords'=>'',
            'cate_name'=>'',
        ]);

    }


    /**
     * 地址列表
     * @return string
     */
    public function actionAddress()
    {
        return $this->render('address.html');
    }
}
