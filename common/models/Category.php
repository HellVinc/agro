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
 * This is the model class for table "category".
 *
 * @property integer $id
 * @property string $name
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property Tag[] $tags
 */
class Category extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;

//    const TYPE_TRADE = 1;
//    const TYPE_CHAT = 2;
//    const TYPE_FINANCE = 3;

    const DEF_F = ['id', 'Name', 'category_id'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'category';
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
            [['name'], 'required'],
            [['status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['name'], 'string', 'max' => 255],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
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
            'Name',
            'tags' => function ($model) {
                /** @var $model Category */
                return Tag::getFields($model->tags, self::DEF_F);
            }
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
                'name',
                'status',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
            ]),
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return '#' . $this->name;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['category_id' => 'id']);
    }

    public function getAdvertisementsBuy()
    {
        return $this->hasMany(Advertisement::className(), ['tag_id' => 'id'])
            ->viaTable('tag', ['category_id' => 'id'])
            ->where(['type' => Advertisement::TYPE_BUY])->count();
    }
}
