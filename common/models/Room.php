<?php

namespace common\models;

use common\components\helpers\ExtendedActiveRecord;
use common\components\traits\errors;
use common\components\traits\findRecords;
use common\components\traits\soft;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "room".
 *
 * @property integer $id
 * @property string $title
 * @property string $description
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
            [['title', 'description', 'category_id'], 'required'],
            [['description'], 'string'],
            [['status', 'created_at', 'updated_at', 'created_by', 'updated_by', 'category_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['category_id'], 'exist', 'filter' => ['category_type' => Category::TYPE_CHAT, 'status' => self::STATUS_ACTIVE], 'targetClass' => Category::className(), 'targetAttribute' => ['category_id' => 'id']],
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
            'description' => 'Description',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }



    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessages()
    {
        return $this->hasMany(Message::className(), ['room_id' => 'id']);
    }

    /**
     * @return bool
     */
    public function beforeDelete()
    {
        foreach ($this->messages as $message) {
            $message->delete();
        }
        return parent::beforeDelete();
    }
}
