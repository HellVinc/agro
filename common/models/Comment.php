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
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "comment".
 *
 * @property integer $id
 * @property integer $advertisement_id
 * @property string $text
 * @property integer $viewed
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property Advertisement $advertisement
 */
class Comment extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;

    const TYPE_UNVIEWED = 0;
    const TYPE_VIEWED = 1;

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
        return 'comment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['advertisement_id', 'viewed', 'status'], 'integer'],
            [['text', 'advertisement_id'], 'required'],
            [['text'], 'string'],
            [['advertisement_id'], 'exist',
                'filter' => [
                    'status' => self::STATUS_ACTIVE,
                ], 'targetClass' => Advertisement::className(),
                'targetAttribute' => [
                    'advertisement_id' => 'id',
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
            'advertisement_id' => 'Advertisement ID',
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

        $result = [
            'id' => $this->id,
            'advertisement_id' => $this->advertisement_id,
            'text' => $this->text,
            'viewed' => $this->viewed,
            'date' => $this->created_at,
            'user' => $this->userInfo,
            'avatar' => User::findOne(['id' => $this->created_by])->photoPath,
        ];
        return $result;
    }
    /**
     * @return array
     */
    public function extraFields()
    {
        return [
            'date' => 'created_at',
            'avatar' => function ($model) {
                return User::findOne(['id' => $this->created_by])->photoPath;
            },
            'user' => function ($model) {
                $user = $model->creator;
                return $model->getCreator() ? $user->first_name . ' ' . $user->last_name : '';
            }
        ];
    }

    /**
     * @param $result
     * @return array
     */
    public static function allFields($result)
    {
        switch (Yii::$app->controller->module->id) {
            case 'v1':
                return self::responseAll($result, [
                    'id',
                    'text',
                    'advertisement_id',
                    'viewed',
                    'created_at' => function($model){
                    return date('Y-m-d', $model->created_at);
                    },
                    'user'=> 'UserInfo',
                    'avatar',
                ]);

            case 'v2':
                return self::responseAll($result, [
                    'id',
                    'text',
                    'status',
                    'advertisement_id',
                    'created_by' => function ($model) {
                        return User::getFields($model->creator, [
                            'id',
                            'phone',
                            'first_name',
                            'last_name',
                        ]);
                    },
                    'created_at',
                    'updated_at',
                ]);
        }
    }


    /**
     * @param $models
     * @return void
     */
    public function changeViewed($models)
    {
        foreach ($models as $model){
            $advertisement = Advertisement::findOne($model['advertisement_id']);
            if($advertisement->created_by === Yii::$app->user->id){
                $comment = Comment::findOne($model['id']);
                $comment->viewed = Comment::TYPE_VIEWED;
                $comment->save();
            }
        }
    }

    /**
     * @return string
     */
    public function getUser()
    {
        $var = User::findOne($this->created_by);
        return [
            'user_id' => $var->id,
            'name' => $var->first_name . ' ' . $var->last_name];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvertisement()
    {
        return $this->hasOne(Advertisement::className(), ['id' => 'advertisement_id']);
    }
}
