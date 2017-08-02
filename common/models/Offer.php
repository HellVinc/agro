<?php

namespace common\models;

use common\components\helpers\ExtendedActiveRecord;
use common\components\traits\errors;
use common\components\traits\findRecords;
use common\components\traits\soft;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "offer".
 *
 * @property integer $id
 * @property string $title
 * @property string $description
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

    const TYPE_NOT_DONE = 0;
    const TYPE_DONE = 1;

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
            [['title', 'description'], 'required'],
            [['done', 'checked', 'viewed', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['title', 'description'], 'string', 'max' => 255],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            [['done'], 'default', 'value' => self::TYPE_NOT_DONE],
            [['done'], 'in', 'range' => [self::TYPE_DONE, self::TYPE_NOT_DONE]],
            [['checked'], 'default', 'value' => self::TYPE_UNCHECKED],
            [['checked'], 'in', 'range' => [self::TYPE_UNCHECKED, self::TYPE_CHECKED]],
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
            'title' => 'Title',
            'description' => 'Description',
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


    /**
     * @param $result
     * @return array
     */
    public static function allFields($result)
    {
        return self::getFields($result, [
            'id',
            'done',
            'title',
            'viewed',
            'status',
            'checked',
            'description',
        ]);
    }

    /**
     * @return array
     */
    public function oneFields()
    {
        return [
            strtolower($this->getClassName()) => self::getFields($this, [
                'id',
                'done',
                'title',
                'viewed',
                'status',
                'checked',
                'description',
            ]),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->viaTable('offer_tag', ['offer_id' => 'id']);
    }
}
