<?php
//访问协议
define('PROTOCOL', $_SERVER['SERVER_PORT']==443 ? 'https' : 'http');

//定义回调URL通用的URL
define('URL_CALLBACK', PROTOCOL.'://www.movemama.com/login/');
//主域
define('DOMAIN', 'bycouturier.com');
//环境配置
define('ENVUC', 'dev');

//UcenterUrl配置
$tmphosts = explode('.', $_SERVER['HTTP_HOST']);
unset($tmphosts[0]);
$tmphosts = implode('.', $tmphosts);
define('UCENTER_URL', PROTOCOL.'://devucenter.' . $tmphosts . '/');
unset($tmphosts);

//时间戳
define('NOWTIME', time());
//版本号
define('VERSION', '20160622');


//所有日志的路径
define('LOG_PATH', "/data/logs/");

//图片上传根目录
define('UPLOAD_IMAGE_FILE_URL', '/Uploads/images/');
define('UPLOAD_IMAGE_FILE', dirname(dirname(__DIR__)) .'/web'. UPLOAD_IMAGE_FILE_URL);
//构建目录名称
define('GULP', 'src');
define('CDN_URL', '/Public/src');
define('__SELF__', strtolower(strip_tags($_SERVER['REQUEST_URI'])));
define('DEFAULT_AVATAR','https://cdn-image.mutantbox.com/201709/e2d887969e5155be1117bfb2688263a1.png');
define("CACHE_PREFIX","bycouturier");
define("CACHE_EXPIRE",300);
return [
    'COOKIE_DOMAIN' => DOMAIN,
    'adminEmail'          => 'admin@mutantbox.com',
    //返回数据格式
    'result'              => ['code' => 0, 'error' => [], 'data' => []],
    'pay'                 => 'http://devpay.bycouturier.com',
    'play'                => 'http://play.movemama.com',
    'LOGURL2'             => '163.44.165.46',
    'LOGPORT2'            => '5515',
    'LOGURL' => '133.130.90.180',
    'LOGPORT' => '8889',
    //url 设置
    'MY_URL'=>array(
        'PAY'  => PROTOCOL.'://devpay.bycouturier.com',
        'PAYS' => 'https://devpay.bycouturier.com',
        'UCENTER' => PROTOCOL.'://ucenter.movemama.com',
        'EMAIL'=>'http://message.mutantbox.com',
        'ShopPay_1' => PROTOCOL.'://devpay.clothesforever.com',
        'ShopPay_2' => PROTOCOL.'://devpay.lovecrunch.com',
        'ShopPay_bycouturier' => PROTOCOL.'://devpay.bycouturier.com',
        'CF'=> PROTOCOL.'://devwww.clothesforever.com',
        //'LC'=> PROTOCOL.'://devwww.lovecrunch.com',
        'LC'=> 'http://www.lovecrunch.com',
        'BS'=> PROTOCOL.'://devwww.bycouturier.com',
        'MUTANTBOX'=>PROTOCOL.'://devwww.movemama.com',
        'M'=> PROTOCOL.'://localtestm.bycouturier.com',
    ),

    'TOKEN' => array(
        'KEY'=>'uow)*^$@!#%&456kj',
        'ucentkey' => '1Fq9uZj9JeJPuje2',
        'PASSKEY' =>'Liberators123!@#',
        'email_token'=>'c227a43454a2fcac3fbb0d9ce8d8cfa7',
        'shop' => 'rZ2Xj7Q77Tv1lKvZ',
        'queue' => '4ZWrfeG2FEl6Llzu',
        'projectKey' => 'knqmtNP7RuyNRUsEN2',
    ),

    /* 文章封面原图片上传相关配置 */
    'ARTICLEPICTURE_UPLOAD' => array(
        'mimes' => '', //允许上传的文件MiMe类型
        'maxSize' => 2 * 1024 * 1024, //上传的文件大小限制 (0-不做限制)
        'exts' => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub' => true, //自动子目录保存文件
        'subName' => array('date', 'Ym'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/images/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt' => '', //文件保存后缀，空则使用原后缀
        'replace' => false, //存在同名是否覆盖
        'hash' => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ),
    'language' => [
        'en-us' => '1',
        'fr-fr' => '2',
        'de-de' => '3',
        'es-es' => '4',
        //         '5' => 'ch-ch',
        'pt-pt' => '6',
        'ar-ar' => '7',
        'el-el' => '8',
        'tr-tr' => '9',
        'pl-pl' => '10',
        //         '11' => 'cs-cs',
    //         '12' => 'it-it',
    //         '13' => 'hu-hu',
        'ro-ro' => '14',
    ],

    //图片服务
    'image_server_app_id'     => '782937352627475',
    'image_server_secret_key' => 'c227a43454a2fcac3fbb0d9ce8d8cfa7',
    'image_server_host'       => 'testimage.movemama.com',
    'image_server_version'    => 'v1',
    'edm' => 'http://testmessage.movemama.com',
];
