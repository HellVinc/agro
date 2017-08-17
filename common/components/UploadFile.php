<?php

namespace common\components;


use common\models\Attachment;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

use yii\helpers\FileHelper;

/**
 * Class UploadFile
 *
 * @property  $fileDir
 */
class UploadFile extends Model
{

    /**
     * @var UploadedFile
     */
    public $file;
    public $files;

    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false],
            [['files'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg', 'maxFiles' => 3],
        ];
    }

    public function upload($name, $row_id, $table, $type)
    {
        $this->file = UploadedFile::getInstanceByName($name);
        if ($this->validate()) {
            $this->name = substr(uniqid(rand(1, 6)), 0, 8);
            $dir = dirname(Yii::getAlias('@app')) . "/$type/$table";

            if (!is_dir($dir)) {
                FileHelper::createDirectory($dir);
            }
            $dir = $dir . "/" . $row_id;
            if (!is_dir($dir)) {
                FileHelper::createDirectory($dir);
            }
            if ($this->file->saveAs($dir . "/" . $this->name . '.' . $this->file->extension)) {
                return $this;
            }
            $this->addError('file', 'File not saved');
        }
        return $this;
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

    public function uploadBase($name, $id,$ext)
    {
        $data = str_replace('data:image/'.$ext.';base64,', '', $name);
        $data = str_replace(' ', '+', $data);
        $data = base64_decode($data); // Decode image using base64_decode
        $file = substr(uniqid(rand(1, 6)), 0, 8) . '.'. $ext;

        $dir = dirname(Yii::getAlias('@app')) . '/photo/users/' . $id;
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir);
        }

        $dir = dirname(Yii::getAlias('@app')) . "/photo/users/" . $id . "/" . $file;
        if (!file_put_contents($dir, $data)) {
           return false;
        }
        return  $file;

    }
}
