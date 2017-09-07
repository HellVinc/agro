<?php
use yii\web\User;

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\console\controllers\FixtureController',
            'namespace' => 'common\fixtures',
          ],
    ],
    'components' => [
        'user' => [
            'class' => User::class,
            'identityClass' => \common\models\User::class,
            'enableAutoLogin' => false,
            'enableSession' => false
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                ],
            ],
        ],
    ],
    'params' => $params,
];
