<?php
namespace common\components;

use yii\base\Model;
use Yii;

class Errors extends Model
{

    public function ModelError($model)
    {
        $errors = $model->getErrors();
        foreach ($errors as $error) {
            return ['error' => $error[0]];
        }
    }
}