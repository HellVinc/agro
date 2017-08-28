<?php

namespace common\models;

//use function foo\func;
//use Yii;

use common\components\helpers\ExtendedActiveRecord;
use common\components\traits\errors;
use common\components\traits\findRecords;
use common\components\traits\soft;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

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
            [['advertisement_id', 'text'], 'required'],
            [['text'], 'string'],
            [['advertisement_id'], 'exist', 'skipOnError' => true, 'targetClass' => Advertisement::className(), 'targetAttribute' => ['advertisement_id' => 'id']],
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
    public function extraFields()
    {
        return [
            'date' => 'created_at',
            'avatar' => function ($model) {
                if ($model->getCreator()) {
                    return $model->creator->photoPath;
                }
                return '';
            },
            'user' => function ($model) {
                $user = $model->creator;
                return $model->getCreator() ? $user->first_name . ' ' . $user->last_name : '';
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
            'advertisement_id',
            'text',
            'viewed',
            'status',
            'date',
            'user',
            'avatar',
            // 'created_by',
            // 'updated_by',
            // 'created_at',
            // 'updated_at',
        ]);
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
                    'viewed',
                    'date',
                    'user',
                    'avatar',
                ]);

            case 'v2':
                return self::responseAll($result, [
                    'id',
                    'text',
                    'status',
                    'advertisement_id',
                    'created_by' => function ($model) {
                        if ($model->creator) {
                            return User::getFields($model->creator, [
                                'id',
                                'phone',
                                'first_name',
                                'last_name',
                            ]);
                        }
                        return null;
                    },
                    'created_at',
                    'updated_at',
                ]);
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvertisement()
    {
        return $this->hasOne(Advertisement::className(), ['id' => 'advertisement_id']);
    }
}
