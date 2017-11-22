<?php

namespace common\models;

use common\components\helpers\ExtendedActiveRecord;
use Yii;
use common\components\traits\errors;
use common\components\traits\soft;
use common\components\traits\findRecords;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "rating".
 *
 * @property integer $id
 * @property integer $rating
 * @property integer $text
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property User[] $users
 */
class Rating extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rating';
    }

    public function fields()
    {
        return ['rating'];
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
            [['rating', 'user_id'], 'required'],
            [['rating', 'user_id', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['text'], 'string'],
            [['user_id'], 'exist',
                'filter' => [
                    'status' => self::STATUS_ACTIVE,
                ], 'targetClass' => User::className(),
                'targetAttribute' => [
                    'user_id' => 'id',
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
            'rating' => 'Rating',
            'text' => 'Text',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    public function oneFields()
    {
        switch (Yii::$app->controller->module->id) {
            case 'v1':
                return [
                    'id' => $this->id,
                    'rating' => $this->rating,
                    'text' => $this->text,
                    'status' => $this->status,
                    'user' => $this->getUserInfo(),
                    'created_at' => date('d.m.Y', $this->created_at),
                    'updated_at' => date('d.m.Y', $this->updated_at),
                ];
            case 'v2':
                return self::getFields($this, [
                    'id',
                    'rating',
                    'text',
                    'viewed',
                    'status',
                    'user' => 'UserInfo',
                    'created_at',
                ])[0];
        }
    }

    /**
     * @param $result
     * @return array
     */
    public static function allFields($result)
    {
        return self::responseAll($result, [
            'id',
            'rating',
            'text',
            'viewed',
            'status',
            'user' => 'UserInfo',
            'created_at',
        ]);
    }

    public function extraFields()
    {
        return [
            'created_at' => function ($model) {
                return date('d.m.Y', $model->created_at);
            },
            'updated_at' => function ($model) {
                return date('d.m.Y', $model->updated_at);
            },
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
