<?php

namespace common\components;
use common\models\Attachment;
use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class UploadModel extends Model
{
    /**
     * @var UploadedFile[]
     */
    public $files;

    public function rules()
    {
        return [
            [['files'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg', 'maxFiles' => 3],
        ];
    }

    public function uploads($id, $table)
    {
        if ($this->validate()) {
            $dir = dirname(Yii::getAlias('@app')) . '/files/' . $table . '/' . $id;
            foreach ($this->files as $file) {
                if (!is_dir($dir)) {
                    FileHelper::createDirectory($dir);
                }
                $file->saveAs($dir . '/' . $file->baseName . '.' . $file->extension);
                $model = new Attachment();
                $model->object_id = $id;
                $model->table = $table;
                $model->extension = $file->extension;
                $model->url = $file->baseName . '.' . $file->extension;
                $model->save();
            }
            return true;
        }
        return false;
    }
}