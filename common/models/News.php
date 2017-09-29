<?php

namespace common\models;

use common\components\helpers\ExtendedActiveRecord;
use common\components\traits\modelWithFiles;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use common\components\traits\errors;
use common\components\traits\soft;
use common\components\traits\findRecords;
use yii\helpers\ArrayHelper;

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
 * @property Attachment attachment
 */
class News extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;
    use modelWithFiles;

    const TYPE_NEWS = 1;
    const TYPE_SERVICES = 2;

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
            [['type', 'url'], 'required'],
            [['text'], 'string'],
            [['type', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['url'], 'url', 'defaultScheme' => 'http'],
            [['title'], 'string', 'max' => 255],
            [['text', 'title'], 'required', 'when' => function ($model) {
                return (int) $model->type === self::TYPE_NEWS;
            }],
            ['type', 'in', 'range' => [self::TYPE_NEWS, self::TYPE_SERVICES]],
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
        if ($this->attachment) {
            return $this->attachment->getFilePath();
        }
        return Yii::$app->request->getHostInfo() . '/photo/user/empty.jpg';
    }

    public function oneFields()
    {
        return self::getFields($this, [
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
        ])[0];
    }

    public static function allFields($result)
    {
        switch (\Yii::$app->controller->module->id) {
            case 'v1':
                return self::responseAll($result, [
                    'id',
                    'title',
                    'text',
                    'url',
                    'type',
                    'img',
                    'created_at',
                    'resource_url' => function ($model) {
                        /** @var $model News */
                        $url = parse_url($model->url);
                        return $url['scheme'] . '://' . $url['host'];
                    }
                ]);

            case 'v2':
                return self::responseAll($result, [
                    'id',
                    'img',
                    'title',
                    'text',
                    'url',
                    'type',
                    'created_at',
                    'updated_at',
                    'resource_url',
                    'status',
                ]);
        }
    }

    public function extraFields()
    {
        return [
            'img' => function($model) {
                return $model->getPhotoPath();
            },
            'created_at' => function ($model) {
                return date('d.m.Y', $model->created_at);
            },
            'updated_at' => function ($model) {
                return date('d.m.Y', $model->updated_at);
            },
            'resource_url' => function($model) {
                /** News @var $model */
                $url = parse_url($model->url);
                return $url['scheme'] . '://' . $url['host'];
            }
        ];
    }

    public function getAttachments()
    {
        return $this->hasMany(Attachment::className(), ['object_id' => 'id'])
            ->andOnCondition([
                'table' => self::tableName(),
                'status' => self::STATUS_ACTIVE
            ]);
    }

    /**
     * @return array|null|ActiveRecord
     */
    public function getAttachment()
    {
        return $this->getAttachments()->orderBy('id desc')->one(); // get last attachment
    }
}
