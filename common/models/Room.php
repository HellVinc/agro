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
 * This is the model class for table "room".
 *
 * @property integer $id
 * @property integer $category_id
 * @property string $title
 * @property string $text
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property Message[] $messages
 */
class Room extends ExtendedActiveRecord
{
    use modelWithFiles;
    use soft;
    use findRecords;
    use errors;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'room';
    }

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
    public function rules()
    {
        return [
            [['title', 'text'], 'required'],
            [['text'], 'string'],
            [['status', 'category_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['title'], 'string', 'max' => 255],
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
            'text' => 'Description',
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
        return self::getFields($this, [
            'id',
            'category_id',
            'title',
            'text',
            'status',
            'user' => 'UserInfo',
            'created_at' => function ($model) {
                /** @var $model Room */
                return date('Y-m-d', $model->created_at);
            },
            'updated_at',
        ]);
    }

    /**
     * @param $result
     * @return array
     */
    public static function allFields($result)
    {
        return self::getFields($result, [
            'id',
            'category_id',
            'title',
            'text',
            'status',
            'user' => 'UserInfo',
            'created_at' => function ($model) {
                /** @var $model Room */
                return date('Y-m-d', $model->created_at);
            },
            'updated_at',
            'favorites',
            'msgUnread'
        ]);
    }

    public function getMsgUnread()
    {
        return (int)Message::find()->where(['room_id' => $this->id, 'viewed' => Comment::UNVIEWED])->count();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessages()
    {
        return $this->hasMany(Message::className(), ['room_id' => 'id']);
    }

    /**
     * @return int|string
     */
    public function getFavorites()
    {
        return (int) (bool) $this->hasMany(Favorites::className(), ['object_id' => 'id'])
            ->andOnCondition(['table' => $this->formName()])
            ->andOnCondition(['created_by' => Yii::$app->user->id])->count();
    }
}
