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
 * @property integer $viewed
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
            [['title', 'text', 'category_id'], 'required'],
            [['text'], 'string'],
            [['status', 'created_at', 'updated_at', 'created_by', 'updated_by', 'category_id', 'viewed'], 'integer'],
            [['title'], 'string', 'max' => 255],
            ['viewed', 'in', 'range' => [0, 1]],
            [['category_id'], 'exist',
                'filter' => [
                    'category_type' => Category::TYPE_CHAT,
                    'status' => self::STATUS_ACTIVE,
                ], 'targetClass' => Category::className(),
                'targetAttribute' => [
                    'category_id' => 'id',
                ]
            ],
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
        switch (\Yii::$app->controller->module->id) {
            case 'v1':
                return self::getFields($this, ['id',
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
            case 'v2':
                return self::responseAll($this, [

                ]);
        }
    }

    /**
     * @param $result
     * @return array
     */
    public static function allFields($result)
    {
        switch (\Yii::$app->controller->module->id) {
            case 'v1':
                return self::responseAll($result, [
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

            case 'v2':
                return self::responseAll($result, [

                ]);
        }
    }

    public function getMsgUnread()
    {
        return (int)Message::find()->where(['room_id' => $this->id, 'viewed' => Comment::TYPE_UNVIEWED])->count();
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
        return (int)(bool)$this->hasMany(Favorites::className(), ['object_id' => 'id'])
            ->andOnCondition(['table' => $this->formName()])
            ->andOnCondition(['created_by' => Yii::$app->user->id])->count();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function beforeDelete()
    {
        foreach ($this->messages as $message) {
            $message->delete();
        }
        return parent::beforeDelete();
    }
}
