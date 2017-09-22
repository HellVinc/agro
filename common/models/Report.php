<?php

namespace common\models;

use common\components\helpers\ExtendedActiveRecord;
use common\components\traits\errors;
use common\components\traits\soft;
use common\components\traits\findRecords;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "report".
 *
 * @property integer $id
 * @property integer $object_id
 * @property string $table
 * @property string $text
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 */
class Report extends ExtendedActiveRecord
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
        return 'report';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'table', 'text'], 'required'],
            [['object_id', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['text'], 'string'],
            [['table'], 'string', 'max' => 255],
            [['table'], 'filter', 'filter' => 'strtolower'],
            [['object_id'], 'existId'],// validate table and record
        ];
    }

    /**
     * Find record by `object_id` in `table`
     */
    public function existId() {
        switch ($this->table) {
            case User::tableName():
                $exist = User::findOne($this->object_id);
                break;

            case Advertisement::tableName():
                $exist = Advertisement::findOne($this->object_id);
                break;

            default:
                $this->addError(
                    'table',
                    'Supported tables: ' . implode(', ', [
                        User::tableName(),
                        Advertisement::tableName(),
                    ])
                );
                return;
        }

        if (!$exist) {
            $this->addError('object_id', 'Item not exist');
        }
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
            'text' => 'Text',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    public function oneFields()
    {
        return self::getFields($this, [
                'id',
                'object_id',
                'table',
                'text',
                'status',
                'created_at',
                'updated_at',
                'created_by',
                'updated_by',
            ]);
    }

    public static function allFields($result)
    {
        return self::responseAll($result, [
            'id',
            'object_id',
            'table',
            'text',
            'status',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
        ]);
    }

    public function extraFields()
    {
        return [
            'created_at' => function ($model) {
                return date('d.m.Y', $model->created_at);
            },
            'updated_at' => function ($model) {
                return date('d.m.Y', $model->updated_at);
            },
        ];
    }
}
