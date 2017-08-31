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
class UploadBase extends Model
{
    /**
     * @var UploadBase
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

    public function uploadBase($name, $id, $ext)
    {
        $data = str_replace('data:image/'.$ext.';base64,', '', $name);
        $data = str_replace(' ', '+', $data);
        $data = base64_decode($data); // Decode image using base64_decode
        $file = hash_file('crc32', $name) . '.'. $ext;

        $dir = dirname(Yii::getAlias('@app')) . '/photo/user' . '/' . $id;
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir);
        }

        $dir = dirname(Yii::getAlias('@app')) . "/photo/user/" . $id . "/" . $file;
        if (!file_put_contents($dir, $data)) {
            return false;
        }
        return  $file;

    }
}
