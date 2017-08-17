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
 * This is the model class for table "advertisement".
 *
 * @property integer $id
 * @property integer $tag_id
 * @property integer $category_id
 * @property string $title
 * @property string $text
 * @property string $latitude
 * @property string $longitude
 * @property integer $trade_type
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property integer $category
 * @property integer $photo
 *
 * @property Tag $tag
 * @property Comment[] $comments
 * @property Attachment[] $attachments
 */
class Advertisement extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;
    use modelWithFiles;

    public $category_id;
    public $photo;

    const TYPE_BUY = 1;
    const TYPE_SELL = 2;

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
        return 'advertisement';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag_id', 'title', 'text', 'trade_type'], 'required'],
            [['tag_id', 'trade_type', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['text', 'latitude', 'longitude'], 'string'],
//            ['trade_type', 'filter', 'filter' => 'intval'],
            [['title'], 'string', 'max' => 255],
            [['latitude', 'longitude'], 'string', 'max' => 32],
            [['tag_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tag::className(), 'targetAttribute' => ['tag_id' => 'id']],
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
            'trade_type' => 'Trade Type',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }


    /**
     * @return array
     */
    public function oneFields()
    {
        return [
            strtolower($this->getClassName()) => self::getFields($this, [
                'id',
                'title',
                'text',
                'trade_type',
                'viewed',
                'status',
                'user' => 'UserInfo',
                'created_at' => function ($model) {
                    return date('Y-m-d', $model->created_at);
                },
                'updated_at',
                'attachments'
            ]),
        ];
    }

    /**
     * @param $result
     * @return array
     */
    public static function allFields($result)
    {
        return self::getFields($result, [
            'id',
            'tag' => function ($model) {
                return $model->tag->name;
            },
            'title',
            'text',
            'trade_type',
            'viewed',
            'status',
            'user' => 'UserInfo',
            'created_at' => function ($model) {
                return date('Y-m-d', $model->created_at);
            },
            'updated_at',
            'attachments'
        ]);
    }

    public function getPhotoPath()
    {
        if ($this->photo) {
            return Yii::$app->request->getHostInfo() . '/files/advertisement/' . $this->id . '/' . $this->photo;
        }
        return Yii::$app->request->getHostInfo() . '/photo/users/empty_book.jpg';
    }

    public function getAttachments()
    {
        return $this->hasMany(Attachment::className(), ['object_id' => 'id'])
            ->andOnCondition([
                'attachment.table' => 'advertisement',
                'table' => self::tableName()
            ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTag()
    {
        return $this->hasOne(Tag::className(), ['id' => 'tag_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['advertisement_id' => 'id']);
    }
}