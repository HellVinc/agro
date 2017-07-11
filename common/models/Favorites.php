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
 * @property integer $parent_id
 * @property string $table
 * @property integer $status
 * @property integer $type
 * @property integer $object_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 */
class Favorites extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;

    const TYPE_DISCUSSION = 1;
    const TYPE_ADVIRTESEMENT = 2;

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
        return 'favorites';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'type', 'created_at', 'created_by'], 'required'],
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
            'type' => 'Type',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    public function getObject(){

        if($this->type == self::TYPE_DISCUSSION){
            return Discussion::findOne($this->object_id);
        }

        if($this->type == self::TYPE_DISCUSSION){
            return Discussion::findOne($this->object_id);
        }

        return null;
    }

    public function getOneFavorite()
    {
        $class = $this->table;
        News::className();
    }

    public function oneFields()
    {

        $result = [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        return $result;
    }

    public function allFields($result)
    {
        $result['models'] = ArrayHelper::toArray($result['models'],
            [
                Category::className() => [
                    'id',
                    'name'
                ],
            ]
        );
        return $result;
    }
}
