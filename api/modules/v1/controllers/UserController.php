<?php

namespace api\modules\v1\controllers;

use common\components\traits\errors;
use common\models\Advertisement;
use common\models\Category;
use common\models\LoginForm;
use common\models\Message;
use common\models\News;
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


class UserController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => QueryParamAuth::className(),
            'tokenParam' => 'auth_key',
            'only' => [
                'update',
                'delete',
            ],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => [
                'update',
                'delete',
            ],
            'rules' => [
                [
                    'actions' => [
                        'update',
                        'delete',
                    ],
                    'allow' => true,
                    'roles' => [
                        '@'
                    ],
                ],
                [
                    'actions' => [
                        'create',

                    ],
                    'allow' => true,
                    'roles' => ['admin'],

                ],
            ],
        ];

        $behaviors['verbFilter'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'all' => ['get'],
                'one' => ['get'],
                'create' => ['post'],
                'update' => ['post'],
                'delete' => ['delete'],
                'check' => ['post'],
            ],
        ];

        return $behaviors;
    }

    public function actionCheck()
    {
        $model = User::findOne(['auth_key' => Yii::$app->request->get('auth_key')]);
        if ($model) {
            return [
                'model' => $model->oneFields(),
                'counts' => User::menu()
            ];
        }
        return ['error' => 'Error. Bad auth_key.'];
    }

    /**
     * @return array
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

    public function actionOne()
    {
        return $this->findModel(Yii::$app->request->get('id'))->oneFields();
    }

    /**
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new User();
        $model->scenario = 'signUp';
        $model->role = User::find()->one() ? User::ROLE_CLIENT : User::ROLE_ADMIN;

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
                $result['counts'] = User::menu();
                return $result;

                // $result['user']['auth_key'] = Yii::$app->user->identity->getAuthKey();
                // return [
                //     'model' => $result,
                //     'counts' => User::menu()
                // ];
            }
            return ['error' => 'Invalid login or password'];

        }
        return ['error' => 'Error. Bad request.'];
    }

    /**
     * Updates an existing User model.
     * @return mixed
     */
    public function actionUpdate()
    {
        $model = User::findOne(['auth_key' => Yii::$app->request->get('auth_key')]);

        if ($model->load(['User' => Yii::$app->request->post()])   && $model->save()) {
//            if(Yii::$app->request->post('password')){
//                return  $model->saveUpdate();
//            }
//          $model->save();
            $model->image_file = 'photo';

             $model->savePhoto();
            return $model;
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
