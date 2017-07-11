<?php
namespace console\controllers;
use Yii;
use yii\console\Controller;
use common\components\rbac\UserRoleRule;
class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll(); //удаляем старые данные
        //Создадим для примера права для доступа к админке
        //Включаем наш обработчик
        $rule = new UserRoleRule();
        $auth->add($rule);
        //Добавляем роли
        $client = $auth->createRole('client');
        $client->description = 'Client';
        $client->ruleName = $rule->name;
        $auth->add($client);
        //Добавляем потомков
        $admin = $auth->createRole('admin');
        $admin->description = 'Admin';
        $admin->ruleName = $rule->name;
        $auth->add($admin);
        $auth->addChild($admin, $client);
    }
}