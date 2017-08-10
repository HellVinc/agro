<?php

namespace common\models;

use common\components\helpers\ExtendedActiveRecord;
use common\components\traits\modelWithFiles;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use common\components\traits\errors;
use common\components\traits\soft;
use common\components\traits\findRecords;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "news".
 *
 * @property integer $id
 * @property string $title
 * @property string $text
 * @property string $url
 * @property string $type
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $photo
 */
class News extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;
    use modelWithFiles;

    public $photo;

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

    public function getNewsCount()
    {
        return News::find()->count();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['text', 'url'], 'string'],
            [['type', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['title'], 'string', 'max' => 255],
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
            'text' => 'Text',
            'url' => 'Url',
            'type' => 'Type',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    public function getPhotoPath()
    {
        if ($this->photo) {
            return Yii::$app->request->getHostInfo() . '/files/news/' . $this->id . '/' . $this->photo;
        }
        return Yii::$app->request->getHostInfo() . '/photo/users/empty.jpg';

    }

    public function oneFields()
    {

        $result = [
            'id' => $this->id,
            'title' => $this->title,
            'text' => $this->text,
            'url' => 'http://myagro/files/skFHvafJvs0.jpg',
            'type' => $this->type,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'img' => $this->getPhotoPath()
        ];
        return $result;
    }

    public static function allFields($result)
    {
        return ArrayHelper::toArray($result,
            [
                News::className() => [
                    'id',
                    'title',
                    'text',
                    'url',
                    'type',
                    'img' => function($model){
            /** News @var $model */
                        return 'http://192.168.0.133/files/skFHvafJvs0.jpg';
                    },
                    'created_at' => function($model) {
                        return date('Y-m-d', $model->created_at);
                    },
                    'url1' => function($model){
                        /** News @var $model */
                        $url = parse_url($model->url);
                        return $url['scheme'] . '://' . $url['host'];
                    }
                ],
            ]
        );
    }

}
