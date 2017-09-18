<?php

namespace common\components\rbac;

use Yii;
use yii\rbac\Rule;
use yii\helpers\ArrayHelper;
use common\models\User;
use common\models\Role;

class UserRoleRule extends Rule
{
    public $name = 'userRole';

    public function execute($user, $item, $params)
    {
        //Получаем массив пользователя из базы
        $user = ArrayHelper::getValue($params, 'user', User::findOne($user));
        if ($user) {
            $role = $user->role; //Значение из поля role базы данных
            switch ($item->name) {
                case 'client_new':
                    return $role === User::ROLE_CLIENT_NEW;
                    break;
                case 'client_blocked':
                    return $role === User::ROLE_CLIENT_BLOCKED;
                    break;
                case 'client':
                    return $role === User::ROLE_CLIENT;
                    break;
                case 'admin':
                    return $role === User::ROLE_ADMIN;
                    break;

                default :
                    return false;
            }
        }
    }
}