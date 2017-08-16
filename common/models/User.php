<?php

namespace common\models;

use common\components\helpers\ExtendedActiveRecord;
use common\components\traits\errors;
use common\components\traits\findRecords;
use common\components\traits\modelWithFiles;
use common\components\traits\soft;
use common\components\UploadFile;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $first_name
 * @property string $middle_name
 * @property string $last_name
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $phone
 * @property string $role
 * @property string $auth_key
 * @property integer $photo
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $password write-only password
 *
 * @property $image_file
 * @property $extension
 *
 * @property $photoPath
 * @property Rating[] $ratings
 *
 * @property Report reports
 */
class User extends ExtendedActiveRecord implements IdentityInterface
{

    use soft;
    use findRecords;
    use errors;
    use modelWithFiles;

    public $password;

    public $image_file;
    public $extension;

    // const STATUS_DELETED = 0;
    // const STATUS_ACTIVE = 10;

    const ROLE_ADMIN = 1;
    const ROLE_CLIENT = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

//    public function fields()
//    {
//        return [
//            'first_name',
//            'last_name',
//            'Phone',
//            'auth_key',
//        ];
//    }

    /**
     * @inheritdoc
     */
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
//            'blameable' => [
//                'class' => BlameableBehavior::className(),
//                'createdByAttribute' => 'created_by',
//                'updatedByAttribute' => 'updated_by'
//            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['phone', 'trim'],
            [['phone'], 'required', 'except' => ['change_pass']],
            ['phone', 'unique', 'message' => 'This phone has already been taken.'],
            ['phone', 'number', 'numberPattern' => '/^0?\d{9}$/', 'message' => 'Invalid phone format, use 9 digit'],
            [['first_name', 'middle_name', 'last_name'], 'string', 'max' => 55],
            [['photo'], 'string', 'max' => 255],
//            [['image_file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg'],
            ['password', 'required', 'on' => 'signUp'],
            ['password', 'string', 'min' => 6],
            ['role', 'default', 'value' => self::ROLE_CLIENT],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
        ];
    }


    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */

    public function signup()
    {
        $this->setPassword($this->password);
        $this->generateAuthKey();
        $this->save();
        return $this;
    }

    public function saveUpdate()
    {
        if (Yii::$app->request->post('password')) {
            $this->setPassword($this->password);
            $this->save();
            return $this;
        }
        return $this->errors;
    }

    public function getPhone()
    {
        return '+380' . $this->phone;
    }

    public function getPhotoPath()
    {
        if ($this->photo) {
            return Yii::$app->request->getHostInfo() . '/photo/user/' . $this->id . '/' . $this->photo;
        }
        return Yii::$app->request->getHostInfo() . '/photo/user/empty.jpg';

    }

    public function getPhotoDir()
    {
        return dirname(Yii::getAlias('@app')) . '/photo/users/' . $this->id . '/' . $this->photo;
    }

    public function savePhoto()
    {
        $result = (new UploadFile())->upload($this->image_file, $this->id, self::tableName(), 'photo');
        if (!$result) {
            return $this->addError('error', 'Image not saved');
        }
        if ($this->photo) {
            $old_photo = $this->photo;
        }
        if ($this->save() && isset($old_photo)) {
            $this->photo = $old_photo;
            if (file_exists($this->photoDir)) {
                unlink($this->photoDir);
            }

            $this->photo = $result->name . '.' . $result->file->extension;
        } else {
            $this->photo = $result->name . '.' . $result->file->extension;
        }
        if ($this->save()) {
            return $this;
        }
        return $this->errors;

    }

    public function getAttachments()
    {
        return $this->hasOne(Attachment::className(), ['object_id' => 'id'])->andOnCondition(['attachment.status' => self::STATUS_ACTIVE]);
    }


    public function getReports()
    {
        return $this->hasMany(Report::className(), ['object_id' => 'id'])
            ->andOnCondition([
                'report.table' => self::tableName()
            ]);
    }

    public function extraFields()
    {
        return [
            'phone' => 'Phone',
            'second_name' => 'middle_name',
            'photo' => 'photoPath',
            'count_reports' => function ($model) {
                return (int)$model->getReports()->count();
            },
        ];
    }

    public function oneFields()
    {
        switch (Yii::$app->controller->module->id) {
            case 'v1':
                return $this->responseOne([
                    'id',
                    'role',
                    'phone',
                    'photo',
                    'auth_key',
                    'first_name',
                    'second_name',// middle_name
                    'last_name',
                    'created_at',
                    'updated_at',
                ]);
            case 'v2':
                return $this->responseOne([
                    'id',
                    'role',
                    'phone',
                    'photo',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'created_at',
                    'updated_at',
                ]);
        }
    }

    public static function allFields($result)
    {
        switch (Yii::$app->controller->module->id) {
            case 'v1':
                return self::responseAll($result, [
                    'id',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'role',
                    'photoPath',
                    'phone',
                    'rating'
                ]);
            case 'v2':
                return self::responseAll($result, [
                    'id',
                    'first_name',
                    'last_name',
                    'count_reports',
                    'role',
                    'photoPath',
                    'phone',
                    'rating'
                ]);
        }
    }


    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @param mixed $token
     * @param null $type
     * @return static
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['auth_key' => $token]);
        // throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by phone
     *
     * @param string $phone
     * @return static|null
     */
    public static function findByPhone($phone)
    {
        return static::findOne(['phone' => $phone, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @return float|int|mixed
     */
    public function getRating()
    {
        $result = 0;
        $ratings = $this->ratings;
        foreach ($ratings as $one) {
            $result += $one['rating'];
        }
        $count = $this->getRatings()->count();
        if ($count == 0) {
            return $result;
        }
        return round($result / $this->getRatings()->count(), 2);
    }

    /**
     * @return mixed
     */
    public static function menu()
    {
        $result['buy'] = (new Query())->select('id')
            ->from('advertisement')
            ->where(['trade_type' => Advertisement::TYPE_BUY])->count();

        $result['sell'] = (new Query())->select('id')
            ->from('advertisement')
            ->where(['trade_type' => Advertisement::TYPE_SELL])->count();

        $result['chat'] = Message::find()->where(['status' => Message::STATUS_ACTIVE])->count();

        $result['news'] = News::find()->where(['status' => News::STATUS_ACTIVE])->count();

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRatings()
    {
        return $this->hasMany(Rating::className(), ['user_id' => 'id']);
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
