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
        $client_new = $auth->createRole('client_new');
        $client_new->ruleName = $rule->name;
        $auth->add($client_new);

        $client = $auth->createRole('client');
        $client->ruleName = $rule->name;
        $auth->add($client);

        $client_blocked = $auth->createRole('client_blocked');
        $client_blocked->ruleName = $rule->name;
        $auth->add($client_blocked);

        //Добавляем потомков
        $admin = $auth->createRole('admin');
        $admin->ruleName = $rule->name;
        $auth->add($admin);

        $auth->addChild($admin, $client);
        $auth->addChild($client, $client_new);


    }
}