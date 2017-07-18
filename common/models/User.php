<?php
namespace common\models;

use common\components\traits\errors;
use common\components\traits\modelWithFiles;
use common\components\traits\soft;
use common\components\traits\findRecords;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
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
 * @property  $photoPath
 * @property Rating[] $ratings
 */
class User extends ActiveRecord implements IdentityInterface
{

    use soft;
    use findRecords;
    use errors;
    use modelWithFiles;

    public $password;
    public $photo;

    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    const ROLE_ADMIN = 1;
    const ROLE_CLIENT = 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    public function fields()
    {
        return [
            'first_name',
            'last_name',
            'Phone',
            'auth_key',
        ];
    }

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
            ['phone', 'number', 'numberPattern' => '/^0?\d{9}$/', 'message' => 'Invalid phone format'],
            [['first_name', 'middle_name', 'last_name'], 'string', 'max' => 55],
            ['password', 'required'],
            ['password', 'string', 'min' => 6],
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

    public function getPhone()
    {
        return '+380' . $this->phone;
    }

    public function getPhotoPath()
    {
        if ($this->photo) {
            return Yii::$app->request->getHostInfo() . '/photo/users/' . $this->id . '/' . $this->photo;
        }
            return Yii::$app->request->getHostInfo() . '/photo/users/empty.jpg';

    }

    public function getPhotoDir()
    {
        return dirname(Yii::getAlias('@app')) . '/photo/users/' . $this->id . '/' . $this->photo;
    }

    public function oneFields()
    {

        $result = [
            'user' => [
                'id' => $this->id,
                'role' => $this->role,
                'phone' => $this->phone,
                'photo' => $this->photoPath,
                'first_name' => $this->first_name,
                'second_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,

            ],
            'label' => $this->attributeLabels()
        ];
        return $result;
    }

    public static function allFields($result)
    {
        return ArrayHelper::toArray($result,

            [
                User::className() => [
                    'id',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'role',
                    'photoPath',
                    'Phone',
                    'rating'
                ],
            ]
        );
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
     * @throws NotSupportedException
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
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

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    public function getRating()
    {
        $result = 0;
        $ratings = $this->ratings;
        foreach ($ratings as $one) {
            $result += $one['rating'];
        }
        $count = $this->getRatings()->count();
        if($count == 0)
        {
            return 0;
        }
        return $result/$this->getRatings()->count();
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
}
