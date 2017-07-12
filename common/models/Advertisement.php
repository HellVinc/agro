<?php

namespace common\models;

use common\components\helpers\ExtendedActiveRecord;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "advertisement".
 *
 * @property integer $id
 * @property integer $category_id
 * @property integer $tag_id
 * @property string $title
 * @property string $text
 * @property string $latitude
 * @property string $longitude
 * @property integer $deal_type
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property Category $category
 * @property Comment[] $comments
 */
class Advertisement extends ExtendedActiveRecord
{
    public static $buy = 1;
    public static $sell = 2;

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
        return 'advertisement';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag_id', 'title', 'text', 'ad_type'], 'required'],
            [['tag_id', 'deal_type', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['text', 'latitude', 'longitude'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['latitude', 'longitude'], 'string', 'max' => 32],
            [['tag_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tag::className(), 'targetAttribute' => ['tag_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'Category ID',
            'title' => 'Title',
            'text' => 'Text',
            'ad_type' => 'Ad Type',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    public function getBuyCount()
    {
        return Advertisement::find()->where(['ad_type' => 1])->count();
    }

    public function getSellCount()
    {
        return Advertisement::find()->where(['ad_type' => 2])->count();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'tag_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTag()
    {
        return $this->hasOne(Tag::className(), ['id' => 'tag_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['advertisement_id' => 'id']);
    }
}