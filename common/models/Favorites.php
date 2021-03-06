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
 * This is the model class for table "favorites".
 *
 * @property integer $id
 * @property integer $object_id
 * @property string $table
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $trade_type
 * @property integer $category_id
 *
 * @property Advertisement $advertisement
 * @property Room $room
 */
class Favorites extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;

    public $trade_type;
    public $category_id;

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
                'updatedByAttribute' => 'updated_by',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_VALIDATE => ['created_by']
                ]
            ]
        ];
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'favorites';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id'], 'required'],
            ['table', 'string'],
            [['table', 'object_id', 'created_by'], 'unique', 'targetAttribute' => ['table', 'object_id', 'created_by']],
            [['object_id', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'object_id' => 'Object ID',
            'table' => 'Table',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }


    public function oneFields()
    {

        $result = [
            'id' => $this->id,
            'object_id' => $this->object_id,
            'table' => $this->table,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        return $result;
    }

    /**
     * @param $result
     * @return array
     */
    public static function allFields($result)
    {
        return self::responseAll($result, [
            'id',
            'status',
            'object' => 'Object',
            'user' => 'UserInfo',
            'created_at' => function ($model) {
                return date('Y-m-d', $model->created_at);
            },
        ]);
    }

    public function getTheme()
    {
        switch ($this->table) {
            case Advertisement::tableName():
                return $this->advertisement->title;
            case Room::tableName():
                return $this->room->title;
        }
        return 'Not Found';
    }

    public function getObject()
    {
        switch ($this->table) {
            case Advertisement::tableName():
                return Advertisement::allFields(Advertisement::findOne($this->object_id));
            case Room::tableName():
                return Room::allFields(Room::findOne($this->object_id));
        }
        return 'Not found';
    }

    public function getAdvertisement()
    {
        return $this->hasOne(Advertisement::className(), ['id' => 'object_id']);
    }

    public function getRoom()
    {
        return $this->hasOne(Room::className(), ['id' => 'object_id']);
    }
}
