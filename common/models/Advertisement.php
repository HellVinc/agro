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
 * @property string $viewed
 * @property string $closed
 * @property string $city
 * @property integer $trade_type
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
 * @property Report[] $reports
 */
class Advertisement extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;
    use modelWithFiles;

    public $category_id;
    public $photo;
//    public $favorites = 0;

    const TYPE_BUY = 1;
    const TYPE_SELL = 2;
    const TYPE_UNVIEWED = 0;
    const TYPE_VIEWED = 1;

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
            [['tag_id', 'trade_type', 'closed', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['text', 'latitude', 'longitude'], 'string'],
            [['city', 'title'], 'string', 'max' => 255],
            [['latitude', 'longitude'], 'string', 'max' => 32],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['viewed'], 'default', 'value' => self::TYPE_UNVIEWED],
            [['viewed'], 'in', 'range' => [self::TYPE_VIEWED, self::TYPE_UNVIEWED]],
            [['closed'], 'in', 'range' => [0, 1]],
            [['tag_id'], 'exist',
                'filter' => [
                    'status' => self::STATUS_ACTIVE,
                ], 'targetClass' => Tag::className(),
                'targetAttribute' => [
                    'tag_id' => 'id',
                ]
            ],
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
            'city' => 'City',
            'trade_type' => 'Trade Type',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }


    /**
     * @return array
     */
    public function oneFields()
    {
        return [
            'id' => $this->id,
            'tag' => $this->tag->name,
            'tag_id' => $this->tag_id,
            'title' => $this->title,
            'text' =>$this->text,
            'city' => $this->city,
            'trade_type' => $this->trade_type,
            'viewed' => $this->viewed,
            'closed' => $this->closed,
            'status' => $this->status,
            'user' => $this->getUserInfo(),
            'created_at' => date('Y-m-d', $this->created_at),
            'updated_at' => $this->updated_at,
            'attachments' => $this->attachments,

        ];
    }

    /**
     * @param $result
     * @return array
     */
    public static function allFields($result)
    {
        switch (Yii::$app->controller->module->id) {
            case 'v1':
                return self::getFields($result, ['id',
                    'tag' => function ($model) {
                        /** @var $model Advertisement */
                        return $model->tag->name;
                    },
                    'tag_id',
                    'title',
                    'text',
                    'city',
                    'trade_type',
                    'viewed',
                    'closed',
                    'status',
                    'user' => 'UserInfo',
                    'created_at' => function ($model) {
                        return date('Y-m-d', $model->created_at);
                    },
                    'updated_at',
                    'attachments',
                    'favorites',
                    'msgUnread'
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
                    'category_id',
                    'tag',
                    'count_reports',
                    'created_by' => function ($model) {
                        if ($model->creator) {
                            return User::getFields($model->creator, [
                                'id',
                                'phone',
                                'first_name',
                                'last_name',
                                'photo'
                            ]);
                        }
                        return null;
                    },
                    'created_at',
                    'updated_at',
                    'attachments'
                ]);
        }
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
            },
            'category_id' => function ($model) {
                return $model->category->id;
            },
            'created_at' => function ($model) {
                return date('d.m.Y', $model->created_at);
            },
            'updated_at' => function ($model) {
                return date('d.m.Y', $model->updated_at);
            },
        ];
    }

//    public static function allAdvs($model)
//    {
//        $result[] = 0;
//        $favModel = Favorites::findAll(['table' => 'advertisement', 'created_by' => Yii::$app->user->id]);
//        foreach ($model as $advOne) {
//            foreach ($favModel as $favOne) {
//                if ($advOne['id'] === $favOne['object_id']) {
//                    $advOne['favorites'] = 1;
//                }
//            }
//            $result[] = $advOne;
//        }
//        return $result;
//    }

    public function getMsgUnread()
    {
        return (int)Comment::find()->where([
            'advertisement_id' => $this->id,
            'viewed' => Comment::TYPE_UNVIEWED,
            'status' => Comment::STATUS_ACTIVE
        ])->count();
    }

    public static function unreadCount()
    {
        return Comment::find()->leftJoin('advertisement', 'advertisement.id = comment.advertisement_id')
            ->where([
                'advertisement.created_by' => Yii::$app->user->id,
                'advertisement.status' => Advertisement::STATUS_ACTIVE,
                'comment.viewed' => Comment::TYPE_UNVIEWED,
                'comment.status' => Comment::STATUS_ACTIVE
            ])
            ->andFilterWhere(['not in', 'comment.created_by', Yii::$app->user->id])
            ->count();
    }

    public static function unreadBuyCount()
    {
        return Comment::find()->leftJoin('advertisement', 'advertisement.id = comment.advertisement_id')
            ->where([
                'advertisement.created_by' => Yii::$app->user->id,
                'advertisement.status' => Advertisement::STATUS_ACTIVE,
                'comment.viewed' => Comment::TYPE_UNVIEWED,
                'comment.status' => 10,
                'trade_type' => Advertisement::TYPE_BUY
            ])->count();
    }

    public static function unreadSellCount()
    {
        return Comment::find()->leftJoin('advertisement', 'advertisement.id = comment.advertisement_id')
            ->where([
                'advertisement.created_by' => Yii::$app->user->id,
                'advertisement.status' => Advertisement::STATUS_ACTIVE,
                'comment.viewed' => Comment::TYPE_UNVIEWED,
                'comment.status' => 10,
                'trade_type' => Advertisement::TYPE_SELL
            ])->count();
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
                'table' => self::tableName(),
                'status' => self::STATUS_ACTIVE
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
     * @return int|string
     */
    public function getFavorites()
    {
        return (int)(bool)$this->hasMany(Favorites::className(), ['object_id' => 'id'])
            ->andOnCondition(['table' => $this->formName()])
            ->andOnCondition(['created_by' => Yii::$app->user->id])->count();
    }

    public function getReports()
    {
        return $this->hasMany(Report::className(), ['object_id' => 'id'])
            ->andOnCondition([
                'report.table' => self::tableName(),
                'report.status' => self::STATUS_ACTIVE,
            ]);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function beforeDelete()
    {
        foreach ($this->reports as $report) {
            $report->delete();
        }

        foreach ($this->comments as $comment) {
            $comment->delete();
        }

        return parent::beforeDelete();
    }
}