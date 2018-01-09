<?php

namespace common\components\traits;

use common\models\Advertisement;
use common\models\Attachment;
use common\models\News;
use Yii;

trait modelWithFiles
{
    // save records
    public function checkFiles()
    {
        $post = Yii::$app->request->post();

        if (!empty($post['file64'])) {
            $uploaded = Attachment::uploadFile64($post['file64'], $this->id, self::tableName());

            if ($uploaded) {
                $attachments = $this->getAttachments()->andFilterWhere(['!=', 'id', $uploaded->id])->all();
                foreach ($attachments as $attachment) {
                    $attachment->delete();
                }
            }
        }

        if (!empty($_FILES['file'])) {
//            if ($this->tablename() === News::tableName()) {
//                foreach ($this->attachments as $img) {
//                    $img->delete();
//                }
//            }
            if ($this->tablename() === Advertisement::tableName()) {
                return Attachment::uploadFiles($this->id, Advertisement::FILES_DIR);
            }

            return Attachment::uploadFiles($this->id, $this->tablename());
        }

        return $this;
    }

    // delte record with his files
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