<?php

namespace common\models;

use common\components\helpers\ExtendedActiveRecord;
use common\components\UploadBase;
use common\components\UploadModel;
use Yii;
use common\components\traits\errors;
use common\components\traits\soft;
use common\components\traits\findRecords;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "attachment".
 *
 * @property integer $id
 * @property integer $object_id
 * @property string $table
 * @property string $extension
 * @property string $url
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $fileDir
 */
class Attachment extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at'
                ]
            ],
            'blameable' => [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'attachment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'table', 'extension', 'url'], 'required'],
            [['object_id', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['table', 'extension', 'url'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return array
     */
    public function oneFields()
    {
        return self::getFields($this, [
            'id',
            'object_id',
            'table',
            'extension',
            'url',
            'status',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
    }

    /**
     * @param $result
     * @return array
     */
    public static function allFields($result)
    {
        return self::responseAll($result, [
            'id',
            'object_id',
            'table',
            'extension',
            'created_at',
            'updated_at',
            'created_at'
        ]);
    }

    public function extraFields()
    {
        return [
            'created_at' => function($model) {
                return date('Y-m-d', $model->created_at);
            },
            'url' => 'filePath',
        ];
    }

    public function fields()
    {
        return [
            'url' => 'filePath',
        ];
    }

    public static function uploadFiles($id, $table)
    {
        $model = new UploadModel();
        $model->files = UploadedFile::getInstancesByName('file');
        $oldFiles = Attachment::find()->where(['object_id' => $id, 'table' => $table, 'status' => Attachment::STATUS_ACTIVE])->count();
        if((count(UploadedFile::getInstancesByName('file')) + $oldFiles) > 3 ){
            return  $model->getErrors('You can load a total of 3 files');
        }
        if($model->uploads($id, $table)){
            return $model;
        }
    }


//    public static function uploadOne($name, $id, $table)
//    {
//        $file = new self();
//        $result = (new UploadFile())->uploads($name, $id, $table);
//        if (!$result) {
//            return $file->addError('error', 'File not saved');
//        }
//        $file->object_id = $id;
//        $file->table = $table;
//        $file->created_at = time();
//        $file->created_by = Yii::$app->user->id;
//        $file->extension = $result->file->extension;
//        $file->url = $result->name . '.' . $result->file->extension;
//        $file->save();
//        return $file;
//    }

    public function remove()
    {
        $className = $this::lastNameClass(static::className());
        $data = [
            'deleted' => 1
        ];
        parent::load([$className => $data]);
        return $this->save();
    }

    public function saveModel($model)
    {
        if (is_array($_FILES)) {
                $file = self::uploadOne($model->id, $model->tableName());
                if ($file && $file->getErrors()) {
                    return $file;
                }
            return $this;
        }

        return self::uploadOne($model->id, $model->tableName());
    }

    public static function removeWithParent($all)
    {
        foreach ($all as $one) {
            $file = Attachment::findOne($one->id);
            if (!$file->remove()) {
                return $file;
            }
            unlink($file->fileDir);
        }
        return true;
    }


    public function getFilePath()
    {
        return Yii::$app->request->hostInfo . '/files/' . $this->table . '/' . $this->object_id .'/'. $this->url;
    }


    public function getFileDir()
    {

        return dirname(Yii::getAlias('@app')) . '/files/' . $this->table . '/' . $this->object_id .'/'. $this->url;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'object_id' => 'Object ID',
            'table' => 'Table',
            'extension' => 'Extension',
            'url' => 'Url',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }


}
