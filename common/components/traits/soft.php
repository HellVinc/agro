<?php

namespace common\components\traits;

use Yii;

trait soft
{
    # class name

    public function load($data, $formName = null)
    {
        if (array_key_exists($this->formName(), $data)) {
            return parent::load($data, $formName);
        }

        return parent::load([$this->formName() => $data], $formName);
    }

    # load

    public static function lastNameClass($class)
    {
        $array = explode('\\', $class);
        return array_pop($array);
    }

    public function remove()
    {
        $data = [
            'status' => 10
        ];
        parent::load([$this->formName() => $data]);
        return $this->save();
    }

    public function saveModel()
    {
        if ($this->isNewRecord) {
            $this->created_by = Yii::$app->user->id;
            $this->created_at = time();
        } else {
            $this->updated_by = Yii::$app->user->id;
            $this->updated_at = time();
        }
        return $this->save();
    }

    public function saveWithCheck()
    {
        //проверяем. существует ли такая запись
        if ($this->findModel()) {
            return $this->addError('error', Yii::t('msg/error', 'Record was added before'));
        }
        $this->created_at = time();
        // сохраняем новую запись
        return $this->save();
    }

    public function saveWithCheckAndRestore()
    {
        //проверяем. существует ли такая запись
        $model = $this->findModel();
        if ($model) {
            if ($model->status == 10) {
                $model->status = 10;
            }
            return $model->save();
//            $this->addError(['number' => Yii::t('msg/error', 'Record was added before')]);
        } else {
            // сохраняем новую запись
            $this->created_at = time();
            return $this->save();
        }
    }

//    public function disable()
//    {
//        $className = $this::lastNameClass(static::className());
//        $data = [
//            'disable' => $this->disable
//        ];
//        parent::load([$className => $data]);
//        return $this->save();
//    }
}