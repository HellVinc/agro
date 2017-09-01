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
 * This is the model class for table "message".
 *
 * @property integer $id
 * @property integer $room_id
 * @property string $text
 * @property integer $viewed
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property Room $room
 * @property Attachment[] $attachments
 */
class Message extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;
    use modelWithFiles;

    const UNVIEWED = 0;
    const VIEWED = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'message';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
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
            [['room_id', 'text'], 'required'],
            [['room_id', 'viewed', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['text'], 'string'],
            [['room_id'], 'exist', 'skipOnError' => true, 'targetClass' => Room::className(), 'targetAttribute' => ['room_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'room_id' => 'Room ID',
            'text' => 'Text',
            'viewed' => 'Viewed',
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
            'room_id',
            'text',
            'viewed',
            'status',
            'user' => 'UserInfo',
            'created_at' => function ($model) {
                /** @var $model Message */
                return date('Y-m-d', $model->created_at);
            },
            'updated_at',
            'attachments'
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
            'room_id',
            'text',
            'viewed',
            'status',
            'user' => 'UserInfo',
            'created_at' => function ($model) {
                /** @var $model Message */
                return date('Y-m-d', $model->created_at);
            },
            'updated_at',
            'attachments'
        ]);
    }

    /**
     * @param $models
     * @return void
     */
    public function changeViewed($models)
    {
        foreach ($models as $model) {
            $room = Room::findOne($model['room_id']);
            if ($room->created_by === Yii::$app->user->id) {
                $message = Message::findOne($model['id']);
                $message->viewed = Message::VIEWED;
                $message->save();
            }
        }
    }

    /**
     * @return int|string
     */
    public static function unreadCount()
    {
        return Room::find()
            ->leftJoin('message', 'message.room_id = room.id')
            ->where([
                'room.created_by' => Yii::$app->user->id,
                'room.status' => Room::STATUS_ACTIVE,
                'message.viewed' => Message::UNVIEWED,
                'message.status' => Message::STATUS_ACTIVE
            ])
            ->count();

//        return Message::find()
//            ->where([
//                'created_by' => Yii::$app->user->id,
//                'viewed' => Message::UNVIEWED
//            ])->count();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoom()
    {
        return $this->hasOne(Room::className(), ['id' => 'room_id']);
    }

    public function getAttachments()
    {
        return $this->hasMany(Attachment::className(), ['object_id' => 'id'])
            ->andWhere(['status' => Attachment::STATUS_ACTIVE])
            ->andOnCondition([
                'attachment.table' => 'message',
                'table' => self::tableName()
            ]);
    }
}
