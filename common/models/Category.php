<?php

namespace common\models;

use common\components\helpers\ExtendedActiveRecord;
use common\components\traits\modelWithFiles;
use common\components\UploadModel;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use common\components\traits\errors;
use common\components\traits\soft;
use common\components\traits\findRecords;
use yii\web\UploadedFile;

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
 * @property Attachment attachment
 * @property Room[] rooms
 */
class Category extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;

    const TYPE_TRADE = 1;
    const TYPE_CHAT = 2;

//    const DEF_F = ['id', 'Name', 'category_id'];

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

    public function oneFields()
    {
        return self::getFields($this, [
            'id',
            'name',
            'category_type',
            'status',
            'img',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ])[0];
    }

    public function extraFields()
    {
        return [
            'img' => function($model) {
                // $model->attachment show older photo after update
                $attachment = $model->getAttachment()->one();
                return $attachment ? $attachment->getFilePath() : null;
            },
            'name' => 'Name',
            'tags' => function ($model) {
                /** @var $model Category */
                return Tag::getFields($model->tags);
                // return $model->getTags()->select('name')->column();
            },
            'created_at' => function($model) {
                return date('d.m.Y', $model->created_at);
            },
            'updated_at' => function($model) {
                return date('d.m.Y', $model->updated_at);
            },
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
                    'img',
                ]);

            case 'v2':
                return self::responseAll($result, [
                    'id',
                    'name',
                    'category_type',
                    'tags',
                    'status',
                    'img',
                ]);
        }
    }

    public function uploadFile()
    {
//        if ((int)$this->category_type !== self::TYPE_TRADE) {
//            return true;
//        }

        $file = new UploadModel(['scenario' => UploadModel::CATEGORY_FILE]);
        $file->imageFile = UploadedFile::getInstanceByName('file');
        $old_image = $this->attachment;

        if (null !== $file->imageFile && $file->validate()) {
            $model = new Attachment();

            $model->url = $file->upload(
                $this->id,
                'files/' . self::tableName()
            );

            $model->extension = $file->imageFile->extension;
            $model->object_id = $this->id;
            $model->table = self::tableName();

            if ($model->save() && $old_image) {
                $old_image->delete();
            }
            else {
                $this->addError('file', $model->errors);
            }
        }

        if (!empty($file->errors) && !empty($file->errors['imageFile'])) {
            $this->addError('file', $file->errors['imageFile']);
        }

        return !$this->errors;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;//'#'.$this->name;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['category_id' => 'id'])
            ->andOnCondition(['tag.status' => self::STATUS_ACTIVE]);
    }

    public function getRooms()
    {
        return $this->hasMany(Room::className(), ['category_id' => 'id'])
            ->andOnCondition(['room.status' => self::STATUS_ACTIVE]);
    }

    /**
     * @return Attachment|\yii\db\ActiveQuery
     */
    public function getAttachment()
    {
        return $this->hasOne(Attachment::className(), ['object_id' => 'id'])
            ->andOnCondition([
                'table' => self::tableName(),
                'status' => self::STATUS_ACTIVE
            ]);
    }
    
    /**
     * Delete rooms/tags in category
     * @throws \Exception
     */
    public function beforeDelete()
    {
        switch ($this->category_type) {
            case self::TYPE_CHAT:
                foreach ($this->rooms as $room) {
                    $room->delete();
                }
                break;

            case self::TYPE_TRADE:
                foreach ($this->tags as $tag) {
                    $tag->delete();
                }
                break;
        }
        return parent::beforeDelete();
    }
}
