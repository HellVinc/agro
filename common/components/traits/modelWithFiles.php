<?php

namespace common\components\traits;

use common\models\Attachment;
use common\models\News;
use Yii;

trait modelWithFiles
{
    # save records

    public function checkFiles()
    {
        if ($_FILES) {
            if ($this->tablename() === News::tableName()) {
                foreach ($this->attachments as $img) {
                    $img->delete();
                }
            }

            return Attachment::uploadFiles($this->id, $this->tablename());
        }
        return $this;
    }

    #delte record with his files
    public function removeFiles()
    {
        if ($this->files) {
            //удаляем записи принадлежности
            $result = Attachment::removeWithParent($this->files);
            if ($result->errors) {
                $this->addError('error', $result->errors);
            }
        }
        return $this;
    }
}