<?php

namespace common\models;

use common\components\helpers\ExtendedActiveRecord;
use common\components\traits\errors;
use common\components\traits\findRecords;
use common\components\traits\modelWithFiles;
use common\components\traits\soft;
use function foo\func;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

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
 * @property integer $trade_type
 * @property integer $viewed
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

    const TYPE_UNVIEWED = 0;
    const TYPE_VIEWED = 1;

    const TYPE_BUY = 1;
    const TYPE_SELL = 2;

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
            [['tag_id', 'title', 'text', 'trade_type'], 'required'],
            [['tag_id', 'trade_type', 'status', 'closed', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['text', 'latitude', 'longitude'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['latitude', 'longitude'], 'string', 'max' => 32],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['viewed'], 'default', 'value' => self::TYPE_UNVIEWED],
            [['viewed'], 'in', 'range' => [self::TYPE_VIEWED, self::TYPE_UNVIEWED]],
            [['closed'], 'in', 'range' => [0, 1]],
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
            'viewed' => 'Viewed',
            'trade_type' => 'Trade Type',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    public function getReports()
    {
        return $this->hasMany(Report::className(), ['object_id' => 'id'])
            ->andOnCondition([
                'report.table' => self::tableName(),
                'report.status' => self::STATUS_ACTIVE,
            ]);
    }

    public function extraFields()
    {
        return [
            'user' => function ($model) {
                if ($model->creator) {
                    return self::getFields($model->creator, [
                        'name' => 'first_name',
                        'surname' => 'last_name',
                        'photo' => 'photoPath'
                    ]);
                }
                return null;
            },
            'count_reports' => function ($model) {
                return (int)$model->getReports()->count();
            },
            'attachments' => function ($model) {
                return [
                    'attachments' => $model->attachments,
                    'count' => count($model->attachments),
                ];
            },
            'trade_type' => function ($model) {
                switch ($model->trade_type) {
                    case self::TYPE_BUY:
                        return 'Купівля';

                    case self::TYPE_SELL:
                        return 'Продаж';
                }
                return '';
            },
            'category' => function ($model) {
                return $model->category->name;
            }
        ];
    }

    /**
     * @return array
     */
    public function oneFields()
    {
        return $this->responseOne([
            'id',
            'title',
            'text',
            'trade_type',
            'tag',
            'viewed',
            'status',
            'user',
            'created_by',
            'created_at',
            'updated_at',
            'attachments'
        ]);
    }

    /**
     * @param $result
     * @return array
     */
    public static function allFields($result)
    {
        switch (Yii::$app->controller->module->id) {
            case 'v1':
                return self::responseAll($result, [
                    'id',
                    'title',
                    'text',
                    'trade_type',
                    'viewed',
                    'status',
                    'user',
                    'created_at',
                    'updated_at',
                    'attachments'
                ]);

            case 'v2':
                return self::responseAll($result, [
                    'id',
                    'title',
                    'text',
                    'trade_type',
                    'viewed',
                    'status',
                    'closed',
                    'category',
                    'tag',
                    'count_reports',
                    'created_by' => function ($model) {
                        if ($model->creator) {
                            return User::getFields($model->creator, ['id', 'phone']);
                        }
                        return null;
                    },
                    'created_at',
                    'updated_at',
                    'attachments'
                ]);
        }
    }

    public function getPhotoPath()
    {
        if ($this->photo) {
            return Yii::$app->request->getHostInfo() . '/files/advertisement/' . $this->id . '/' . $this->photo;
        }

        return Yii::$app->request->getHostInfo() . '/photo/users/empty_book.jpg';
    }

    public function getAttachments()
    {
        return $this->hasMany(Attachment::className(), ['object_id' => 'id'])
            ->andOnCondition([
                'attachment.table' => 'advertisement',
                'table' => self::tableName()
            ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTag()
    {
        return $this->hasOne(Tag::className(), ['id' => 'tag_id']);
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->tag->category;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['advertisement_id' => 'id']);
    }

    /**
     * @return bool
     */
    public function beforeDelete()
    {
        foreach ($this->reports as $report) {
            $report->delete();
        }
        return parent::beforeDelete();
    }
}