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
 * This is the model class for table "advertisement".
 *
 * @property integer $id
 * @property integer $tag_id
 * @property integer $category_id
 * @property string $title
 * @property string $text
 * @property string $latitude
 * @property string $longitude
 * @property integer $type
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property integer $category
 * @property integer $photo
 *
 * @property Tag $tag
 * @property Comment[] $comments
 * @property Attachment[] $attachments
 */
class Advertisement extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;
    use modelWithFiles;

    public $category_id;
    public $photo;

    const NOT_DELETED = 10;
    const DELETED = 0;

    const TYPE_BUY = 1;
    const TYPE_SELL = 2;
    const TYPE_CHAT = 3;
    const TYPE_FINANCE = 4;

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
            [['tag_id', 'title', 'text', 'type'], 'required'],
            [['tag_id', 'type', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
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
            'title' => 'Title',
            'text' => 'Text',
            'type' => 'Type',
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
            'title' => $this->title,
            'text' => $this->text,
            'type' => $this->type,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'files' => $this->attachments
        ];
        return $result;
    }

    public static function allFields($result)
    {
        return ArrayHelper::toArray($result,
            [
                Advertisement::className() => [
                    'id',
                    'title',
                    'text',
                    'type',
                    'created_at',
                    'updated_at',
                    'created_by',
                    'attachments'
                ],
            ]
        );
    }

    public function getPhotoPath()
    {
        if ($this->photo) {
            return Yii::$app->request->getHostInfo() . '/files/advertisement/' . $this->id . '/' . $this->photo;
        }
            return Yii::$app->request->getHostInfo() . '/photo/books/empty_book.jpg';

    }


    public function getBuyCount()
    {
        return Advertisement::find()->where(['ad_type' => Advertisement::TYPE_BUY])->count();
    }

    public function getSellCount()
    {
        return Advertisement::find()->where(['ad_type' => Advertisement::TYPE_SELL])->count();
    }

    public function getAttachments()
    {
        return $this->hasMany(Attachment::className(), ['object_id' => 'id'])->andOnCondition(['attachment.status' => self::NOT_DELETED]);
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