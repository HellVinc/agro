<?php

namespace common\models;

use common\components\helpers\ExtendedActiveRecord;
use common\components\traits\errors;
use common\components\traits\findRecords;
use common\components\traits\modelWithFiles;
use common\components\traits\soft;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "news".
 *
 * @property integer $id
 * @property string $title
 * @property string $text
 * @property string $url
 * @property string $type
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $photo
 */
class News extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;
    use modelWithFiles;

    public $photo;

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

    public function getNewsCount()
    {
        return News::find()->count();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['text', 'url', 'title'], 'required'],
            [['text', 'url'], 'string'],
            [['type', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['title'], 'string', 'max' => 255],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'text' => 'Text',
            'url' => 'Url',
            'type' => 'Type',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    public function getPhotoPath()
    {
        if ($this->photo) {
            return Yii::$app->request->getHostInfo() . '/files/news/' . $this->id . '/' . $this->photo;
        }
		
        return Yii::$app->request->getHostInfo() . '/photo/users/empty.jpg';
    }

    public function extraFields()
    {
        return [
            'img' => $this->getPhotoPath(),
            'created_at' => function($model) {
                return date('Y-m-d', $model->created_at);
            },
            'url' => function($model) {
                return 'http://192.168.0.118/files/skFHvafJvs0.jpg';
            },
            'resource_url' => function($model) {
                /** News @var $model */
                $url = parse_url($model->url);
                return $url['scheme'] . '://' . $url['host'];
            }
        ];
    }

    /**
     * @return array
     */
    public function oneFields()
    {
        return $this->responseOne([
            'id',
            'title',
            'text',
            'url',
            'type',
            'status',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
            'img',
        ]);
    }

    public static function allFields($result)
    {
        return self::responseAll($result, [
            'id',
            'title',
            'text',
            'url',
            'type',
            'img',
            'created_at',
            'resource_url',
        ]);
    }

}
