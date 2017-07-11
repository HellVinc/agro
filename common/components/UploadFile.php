<?php

namespace common\components;


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
    public $name;
    public $error;

    public function rules()
    {
        return [
            [['name'], 'string'],
            [['file'], 'file', 'skipOnEmpty' => false],
        ];
    }

    public function upload($name, $row_id, $table)
    {
        $this->file = UploadedFile::getInstanceByName($name);
        if ($this->validate()) {
            $this->name = substr(uniqid(rand(1, 6)), 0, 8);
            $dir = dirname(Yii::getAlias('@app')) . '/files/' . $table;
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
