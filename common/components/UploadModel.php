<?php

namespace common\components;

use common\models\Attachment;
use Yii;
use yii\base\ErrorException;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class UploadModel extends Model
{
    /**
     * @var UploadedFile[]
     */
    public $files;
    /** @var  UploadedFile $imageFile */
    public $imageFile;

    const ONE_FILE = 'oneFile';
    const CATEGORY_FILE = 'categoryFile';

    public function rules()
    {
        return [
            [['files'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg', 'maxFiles' => 3, 'on' => 'default'],
            [['imageFile'], 'file', 'extensions' => 'png, jpg', 'on' => 'oneFile'],
            [['imageFile'], 'file', 'extensions' => 'png', 'maxFiles' => 1, 'on' => 'categoryFile'],//, 'skipOnError'=> false, 'skipOnEmpty' => false
        ];
    }

    public function upload($id, $path)
    {
        if ($this->validate()) {
            $dir = dirname(Yii::getAlias('@app')) . '/' . $path  . '/' . $id;
            if (!is_dir($dir)) {
                FileHelper::createDirectory($dir);
            }
            $name = hash_file('crc32', $this->imageFile->tempName);
            $this->imageFile->saveAs($dir . '/' . $name . '.' . $this->imageFile->extension);
            return  $name . '.' . $this->imageFile->extension;
        }
        return false;
    }

    public function uploads($id, $table)
    {
        if ($this->validate()) {
            $dir = dirname(Yii::getAlias('@app')) . '/files/' . $table . '/' . $id;
            if (!is_dir($dir)) {
                FileHelper::createDirectory($dir);
            }
            foreach ($this->files as $file) {
                $name = hash_file('crc32', $file->tempName);
                $file->saveAs($dir . '/' . $name . '.' . $file->extension);
                $model = new Attachment();
                $model->object_id = $id;
                $model->table = $table;
                $model->extension = $file->extension;
                $model->url = $name . '.' . $file->extension;
                $model->save();
            }
            return true;
        }
        return false;
    }

    public static function uploadBase($name, $id, $table)
    {
        $data = str_replace(array('data:image/jpg;base64,', ' '), array('', '+'), $name);
        $data = base64_decode($data); // Decode image using base64_decode
        $file = mt_rand(10000, 900000) . '.jpg';

        $dir = dirname(Yii::getAlias('@app')) . $table . $id;
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir);
        }

        $dir = dirname(Yii::getAlias('@app')) . $table . $id . '/' . $file;
        if (!file_put_contents($dir, $data)) {
            return false;
        }
        return $file;
    }

    public static function uploadDataURL($data, $id, $table)
    {
        $file = [];
        if (!preg_match('/data:([^;]*);base64,(.*)/', $data, $matches)) {
            throw new ErrorException('Wrong Data URL', 400);
        }

        switch ($matches[1]) {
            case 'image/png':
                $file['extension'] = 'png';
                break;

            case 'image/jpg':
            case 'image/jpeg':
                $file['extension'] = 'jpg';
                break;

            default:
                throw new ErrorException('Unsupported mime type', 400);
        }

        $hash = hash('sha1', $matches[2]); // base64 hash
        $file['name'] = $hash . '.' . $file['extension'];
        $dir = dirname(Yii::getAlias('@app')) . '/files/' . $table . '/' . $id;
        $path = $dir . '/' . $file['name'];

        if (!file_exists($path)) {
            $content = base64_decode(
                str_replace(' ', '+', $matches[2])
            );

            if (!$content) { // || !imagecreatefromstring($content)
                throw new ErrorException('Invalid base64', 400);
            }

            if (!is_dir($dir)) {
                FileHelper::createDirectory($dir);
            }

            if (!file_put_contents($path, $content)) {
                return false;
            }
        }

        return $file;
    }
}