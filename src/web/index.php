<?php
//echo file_get_contents('maintenance.html');
//exit;

//常量定义
define('YII_ENV', isset($_SERVER['YII_ENV']) ? $_SERVER['YII_ENV'] : 'dev');    //dev prod test
define('WEBPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);
if( YII_ENV === 'prod' ){
    define('YII_DEBUG', false);
}else{
    define('YII_DEBUG',true);
}
// comment out the following two lines when deployed to production
define('ENV', YII_ENV);

define('BIND_MODULE','wedding');


//引入自定义类库
require(__DIR__ . '/../Library/Ucenter/autoload.php');
require(__DIR__ . '/../Library/Facebook/autoload.php');
require(__DIR__ . '/../Library/ShopPay/autoload.php');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/web.php');
//check LANGUAGE
require(__DIR__ . '/../filters/LangFilter.php');

(new yii\web\Application($config))->run();
