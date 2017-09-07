<?php

namespace api\modules\v2;

/**
 * v2 module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'api\modules\v2\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

//        \Yii::$app->user->enableSession = false;
//        \Yii::$app->user->enableAutoLogin = false;

        // custom initialization code goes here
    }
}
