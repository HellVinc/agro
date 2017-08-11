<?php

namespace common\models;

use common\components\helpers\ExtendedActiveRecord;
use common\components\UploadFile;
use Yii;
use common\components\traits\errors;
use common\components\traits\soft;
use common\components\traits\findRecords;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

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
    const NOT_DELETED = 10;
    const DELETED = 0;

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

    public function oneFields()
    {

        $result = [
            'id' => $this->id,
            'object_id' => $this->object_id,
            'table' => $this->table,
            'extension' => $this->extension,
            'url' => $this->url,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        return $result;
    }

    public static function allFields($result)
    {
        return ArrayHelper::toArray($result,
            [
                Advertisement::className() => [
                    'id',
                    'object_id',
                    'table',
                    'extension',
                    'created_at',
                    'updated_at',
                    'created_by'
                ],
            ]
        );
    }

    public function fields()
    {
        $this->url  = $this->filePath;
        return parent::fields();
    }


    public static function uploadOne($name, $id, $table)
    {
        $file = new self();
        $result = (new UploadFile())->upload($name, $id, $table, 'files');
        if (!$result) {
            return $file->addError('error', 'File not saved');
        }
        $file->object_id = $id;
        $file->table = $table;
        $file->created_at = time();
        $file->created_by = Yii::$app->user->id;
        $file->extension = $result->file->extension;
        $file->url = $result->name . '.' . $result->file->extension;
        $file->save();
        return $file;
    }

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
            foreach ($_FILES as $name => $one) {
                $file = self::uploadOne($name, $model->id, $model->tableName());
                if ($file && $file->getErrors()) {
                    return $file;
                }
            }
            return $this;
        }
            return self::uploadOne('file', $this->id, $this->tableName());
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
//        return Yii::$app->request->hostInfo . '/files/' . $this->table . '/' . $this->object_id .'/'. $this->url;
        return 'http://192.168.0.118/files/skFHvafJvs0.jpg';
    }

    public function getFileDir()
    {

        return dirname(Yii::getAlias('@app')) . '/files/' . $this->table . '/' . $this->object_id .'/'. $this->url;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreator()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdater()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
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
