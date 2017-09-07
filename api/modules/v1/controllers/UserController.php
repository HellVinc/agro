<?php

namespace api\modules\v1\controllers;

use common\components\sms\Smsru;
use common\components\traits\errors;
use common\components\UploadModel;
use common\models\Advertisement;
use common\models\Category;
use common\models\Feedback;
use common\models\LoginForm;
use common\models\Message;
use common\models\News;
use common\models\Rating;
use common\models\search\AdvertisementSearch;
use common\models\search\FeedbackSearch;
use common\models\User;
use frontend\models\SignupForm;
use Yii;
use common\models\search\UserSearch;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;


class UserController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => QueryParamAuth::className(),
            'tokenParam' => 'auth_key',
            'only' => [
                'check',
                'update',
                'delete',
                'add-feedback'
            ],
        ];
//        $behaviors['access'] = [
//            'class' => AccessControl::className(),
//            'only' => [
//                'update',
//                'delete',
//            ],
//            'rules' => [
//                [
//                    'actions' => [
//                        'update',
//                        'delete',
//                    ],
//                    'allow' => true,
//                    'roles' => [
//                        '@'
//                    ],
//                ],
//                [
//                    'actions' => [
//                        'create',
//
//                    ],
//                    'allow' => true,
//                    'roles' => ['admin'],
//
//                ],
//            ],
//        ];

        $behaviors['verbFilter'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'all' => ['get'],
                'one' => ['get'],
                'pass-reset' => ['get'],
                'create' => ['post'],
                'add-feedback' => ['post'],
                'update' => ['post'],
                'delete' => ['delete'],
                'check' => ['post'],
            ],
        ];

        return $behaviors;
    }

    public function actionAddFeedback()
    {
        $model = new Rating();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model->oneFields();
        }
        return ['errors' => $model->errors];
    }

    public function actionCheck()
    {
        $model = User::findOne(['auth_key' => Yii::$app->request->get('auth_key')]);
        if ($model) {
            return [
                'model' => $model->oneFields(),
                'counts' => User::menu(),
                'unread_messages' => User::unreadMessages()
            ];
        }
        return ['error' => 'Error. Bad auth_key.'];
    }

    public function actionUserFeedback()
    {
        $user = $this->findModel(Yii::$app->request->get('id'));
        return [
            'user' => $user->oneFields(),
            'rating' => [
                'user_rating' => $user->getRating(),
                'mark_count' => Rating::find()->where(['user_id' => $user->id])->count()
            ],
            'feedback' => Rating::allFields(Rating::findAll(['user_id' => $user->id]))
        ];
    }

    /**
     * @return string
     */
    public function actionAll()
    {
        $model = new UserSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get());
        return [
            'models' => User::allFields($dataProvider->getModels()),
            'count_model' => $dataProvider->getTotalCount()
        ];
    }

    /**
     * @return array
     */
    public function actionOne()
    {
        if(Yii::$app->request->get('phone')){
            $user = $this->findModel(Yii::$app->request->get('phone'));
            $model = new AdvertisementSearch();
            $dataProvider = $model->searchAll($user->id);
            $models = Advertisement::allFields($dataProvider->getModels());
            return [
                'model' => Advertisement::allFields($models),
                'count_model' => $dataProvider->getTotalCount(),
                'page_count' => $dataProvider->pagination->pageCount,
                'page' => $dataProvider->pagination->page + 1
            ];
        }
        return $this->findModel(Yii::$app->request->get('id'))->oneFields();
    }

    /**
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new User();
        $model->scenario = 'signUp';
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            return $model->signup();
        }
        return $model->errors;
    }

    public function actionLogin()
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post(), "")) {
            if ($model->login()) {
                $result = Yii::$app->user->identity->oneFields();
                return [
                    'model' => $result,
                    'counts' => User::menu(),
                    'unread_messages' => User::unreadMessages()
                ];
            }
            return ['error' => 'Invalid login or password'];
        }
        return ['error' => 'Error. Bad request.'];
    }

    /**
     * @param $phone
     * @return array
     */
    public function actionPassReset($phone)
    {
        return User::passwordReset($phone);
    }

    /**
     * Updates an existing User model.
     * @return mixed
     */
    public function actionUpdate()
    {
        $model = User::findOne(['auth_key' => Yii::$app->request->get('auth_key')]);
        if ($model->load( Yii::$app->request->post())) {
            if($_FILES){
                $file = new UploadModel(['scenario' => UploadModel::ONE_FILE]);
                $file->imageFile = UploadedFile::getInstanceByName('file');
                $model->photo = $file->upload($model->id, 'photo/user');
            }
            $model->image_file = Yii::$app->request->post('file');
            $model->saveUpdate();
            return $model->oneFields();
        }
        return ['errors' => $model->errors];
    }

    /**
     * Deletes an existing Advertisement model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        return $this->findModel($id)->delete();
    }


    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            if ($model->status !== 0) {
                return $model;
            }
            throw new NotFoundHttpException('The record was archived.');
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
