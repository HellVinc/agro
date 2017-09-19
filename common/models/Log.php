<?php

namespace common\models;

use common\components\traits\errors;
use common\components\traits\soft;
use common\components\traits\findRecords;
use Yii;
use common\components\helpers\ExtendedActiveRecord;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "log".
 *
 * @property integer $id
 * @property string $object_id
 * @property string $table
 * @property string $action_name
 * @property integer $created_at
 * @property integer $created_by
 *
 * @property Comment $comment
 * @property Advertisement $advertisement
 * @property Favorites $favorites
 */
class Log extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;

    const LOG_ADV = 'Додано оголошення';
    const LOG_COMMENT = 'Ви прокоментували';
    const LOG_FAVORITES = 'Додано до обраних';


    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
//                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at'
                ]
            ],
            'blameable' => [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
//                'updatedByAttribute' => 'updated_by'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['table', 'action_name'], 'string', 'max' => 50],
            [['object_id'], 'integer'],
        ];
    }

    public static function allFields($result)
    {
        return self::responseAll($result, [
            'id',
            'Message',
//            'advertisement',
//            'comment',
//            'favorites'
        ]);
    }

//    public function getObject()
//    {
//        return $this->table::find()->where(['id' => $this->object_id])->one()->limit(1);
//    }

    public function getData()
    {
        return date('Y-m-d, H:i:s', $this->created_at);
    }

    public function getMessage()
    {
        switch ($this->table) {
            case Advertisement::tableName():
//                return Log::LOG_ADV . $this->advertisement->title . ', ' . $this->getData();
                return [
                    'id' => $this->advertisement->id,
                    'tags' => $this->advertisement->tag->name,
                    'type' => Advertisement::tableName(),
                    'message' => Log::LOG_ADV,
                    'title' => $this->advertisement->title,
                    'date' => $this->getData()
                ];
            case Comment::tableName():
//                return Log::LOG_COMMENT . $this->comment->advertisement->title . ', ' . $this->getData();
                return [
                    'id' => $this->comment->id,
                    'type' => Comment::tableName(),
                    'message' => Log::LOG_COMMENT,
                    'title' => $this->comment->advertisement->title,
                    'date' => $this->getData()
                ];
            case Favorites::tableName():
//                return 'Тема ' . $this->favoritesTitles() . Log::LOG_FAVORITES . ', ' . $this->getData();
                return [
                    'id' => $this->favorites ? $this->favorites->id : 'DELETED',
                    'type' => Favorites::tableName(),
                    'message' => Log::LOG_FAVORITES,
                    'title' => $this->favoritesTitles(),
                    'date' => $this->getData()
                ];
        }
        return 'Помилка';
    }

    public function favoritesTitles()
    {
        if($this->favorites){
            switch ($this->favorites->table) {
                case Advertisement::tableName():
                    return $this->favorites->advertisement->title;
                case Room::tableName():
                    return $this->favorites->room->title;
            }
            return 'Not Found';
        }
       return 'DELETED';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvertisement()
    {
        return $this->hasOne(Advertisement::className(), ['id' => 'object_id'])
            ->andOnCondition(['created_by' => Yii::$app->user->id]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComment()
    {
        return $this->hasOne(Comment::className(), ['id' => 'object_id'])
            ->andOnCondition(['created_by' => Yii::$app->user->id]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFavorites()
    {
        return $this->hasOne(Favorites::className(), ['id' => 'object_id'])
            ->andOnCondition(['created_by' => Yii::$app->user->id]);
    }
}
