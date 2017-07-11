<?php

namespace common\components\traits;

use Yii;
use common\models\File;

trait modelWithFiles
{
    # save records

    public function checkFiles()
    {
        if ($_FILES) {
            $file = new File();
            $res = $file->saveModel($this);
            if ($res && $res->getErrors()) {
                $this->addError('error', $res->getErrors());
            };
        }
        return $this;
    }

    #delte record with his files
    public function removeFiles()
    {
        if ($this->files) {
            //удаляем записи принадлежности
            $result = File::removeWithParent($this->files);
            if ($result->errors) {
                $this->addError('error', $result->errors);
            }
        }
        return $this;
    }
}