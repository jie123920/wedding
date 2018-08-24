<?php
namespace app\modules\wedding;
class Module extends \yii\base\Module {
    public $controllerNamespace = 'app\modules\wedding\controllers';

    public function init() {
        parent::init();
        $config = require __DIR__ . '/config/web.php';
        \Yii::configure(\Yii::$app, $config);
        \Yii::setAlias('@module', __DIR__);
    }
}
