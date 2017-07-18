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
 * @property OfferTag[] $offerTags
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
            [['title', 'description'], 'required'],
            [['status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['title', 'description'], 'string', 'max' => 255],
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
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->viaTable('offer_tag', ['offer_id' => 'id']);
    }
}
