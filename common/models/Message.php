<?php

namespace common\models;

use common\components\helpers\ExtendedActiveRecord;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

use common\components\traits\errors;
use common\components\traits\soft;
use common\components\traits\findRecords;
/**
 * This is the model class for table "message".
 *
 * @property integer $id
 * @property integer $discussion_id
 * @property integer $user_id
 * @property string $text
 * @property integer $checked
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property Discussion $discussion
 */
class Message extends ExtendedActiveRecord
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
        return 'message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['discussion_id', 'user_id', 'text', 'created_at', 'created_by'], 'required'],
            [['discussion_id', 'user_id', 'checked', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['text'], 'string'],
            [['discussion_id'], 'exist', 'skipOnError' => true, 'targetClass' => Discussion::className(), 'targetAttribute' => ['discussion_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'discussion_id' => 'Discussion ID',
            'user_id' => 'User ID',
            'text' => 'Text',
            'checked' => 'Checked',
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
    public function getDiscussion()
    {
        return $this->hasOne(Discussion::className(), ['id' => 'discussion_id']);
    }
}
