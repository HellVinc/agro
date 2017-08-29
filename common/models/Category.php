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
 * @property string $category_type
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property Tag[] $tags
 * @property Advertisement[] $advertisementsBuy

 */
class Category extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;

    const TYPE_TRADE = 1;
    const TYPE_CHAT = 2;

    const DEF_F = ['id', 'name', 'category_id'];

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
            [['name', 'category_type'], 'required'],
            [['category_type', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['name'], 'string', 'max' => 255],
            ['category_type', 'in', 'range' => [self::TYPE_TRADE, self::TYPE_CHAT]],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
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
            'category_type' => 'Category type',
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
            'name' => 'Name',
            'tags' => function ($model) {
                /** @var $model Category */
                return Tag::getFields($model->tags, self::DEF_F);
                // return $model->getTags()->select('name')->column();
            }
        ];
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
                    'name',
                    'category_type',
                    'tags',
                ]);

            case 'v2':
                return self::responseAll($result, [
                    'id',
                    'name',
                    'category_type',
                    'tags',
                    'status',
                ]);
        }
    }

    /**
     * @return array
     */
    /*public function oneFields()
    {
        return $this->responseOne([
            'id',
            'name',
            'category_type',
            'status',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
    }*/

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
        return $this->hasMany(Tag::className(), ['category_id' => 'id'])->andWhere(['status' => self::STATUS_ACTIVE]);
    }


    public function getRooms()
    {
        return $this->hasMany(Room::className(), ['category_id' => 'id'])->andOnCondition(['room.status' => self::STATUS_ACTIVE]);
    }

    /**
     * Delete rooms in category
     */
    public function beforeDelete()
    {
        if ($this->category_type == self::TYPE_CHAT) {
            foreach ($this->rooms as $room) {
                $room->delete();
            }
        }
        return parent::beforeDelete();
    }
}
