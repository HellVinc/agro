<?php

namespace common\models;

use Yii;
use common\components\helpers\ExtendedActiveRecord;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use common\components\traits\errors;
use common\components\traits\soft;
use common\components\traits\findRecords;
/**
 * This is the model class for table "offer".
 *
 * @property integer $id
 * @property string $text
 * @property string $viewed
 * @property string $checked
 * @property string $done
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property Tag[] $tags
 */
class Offer extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;

    const TYPE_UNVIEWED = 0;
    const TYPE_VIEWED = 1;

    const TYPE_UNCHECKED = 0;
    const TYPE_CHECKED = 1;

    const TYPE_DONE = 0;
    const TYPE_NOT_DONE = 1;

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
        return 'offer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['text'], 'required'],
            [['viewed', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['text'], 'string', 'max' => 255],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            [['viewed'], 'default', 'value' => self::TYPE_UNVIEWED],
            [['viewed'], 'in', 'range' => [self::TYPE_UNVIEWED, self::TYPE_VIEWED]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'viewed' => 'Viewed',
            'checked' => 'Checked',
            'done' => 'Done',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    public function extraFields()
    {
        return [
            'created_by' => function ($model) {
                if ($model->creator) {
                    return User::getFields($model->creator, [
                        'id',
                        'phone',
                        'first_name',
                        'last_name',
                        'photo'
                    ]);
                }
                return null;
            },
            'created_at' => function ($model) {
                return date('d.m.Y', $model->created_at);
            },
            'updated_at' => function ($model) {
                return date('d.m.Y', $model->updated_at);
            },
            'description' => 'text'
        ];
    }

    /**
     * @return array
     */
    public function oneFields()
    {
        switch (\Yii::$app->controller->module->id) {
            case 'v1':
                return self::getFields($this, [
                    'id',
                    'text',
                    'status',
                    'user' => 'UserInfo',
                    'created_at',
                    'updated_at',
                ])[0];

            case 'v2':
                return self::getFields($this, [
                    'id',
                    'viewed',
                    'description',
                    'created_at',
                    'created_by',
                    'status',
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
                    'text',
                    'status',
                    'user' => 'UserInfo',
                    'created_at',
                    'updated_at',
                ]);

            case 'v2':
                return self::responseAll($result, [
                    'id',
                    'viewed',
                    'description',
                    'created_at',
                    'created_by',
                    'status',
                ]);
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->viaTable('offer_tag', ['offer_id' => 'id']);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->isNewRecord) {
            Yii::$app
                ->mailer
                ->compose(
                    ['html' => 'newOffer-html', 'text' => 'newOffer-text'],
                    ['offer' => $this]
                )
                ->setFrom([Yii::$app->params['supportEmail'] => 'Agro new offer'])
                ->setTo(Yii::$app->params['offerEmail'])
                ->setSubject('Received a new offer for Agro')
                ->send();
        }

        return true;
    }
}
