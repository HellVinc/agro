<?php

namespace common\models;

use Yii;
use common\components\helpers\ExtendedActiveRecord;
use common\components\traits\errors;
use common\components\traits\findRecords;
use common\components\traits\soft;

/**
 * This is the model class for table "rating".
 *
 * @property integer $id
 * @property integer $rating
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property User[] $users
 */
class Rating extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rating';
    }

    public function fields()
    {
        return ['rating'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rating'], 'required'],
            [['rating', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rating' => 'Rating',
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
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
