<?php

namespace api\modules\v1\controllers;

use common\models\Advertisement;
use common\models\Category;
use common\models\LoginForm;
use common\models\News;
use common\models\User;
use frontend\models\SignupForm;
use Yii;
use common\models\search\UserSearch;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;


class UserController extends Controller
{
//    public function behaviors()
//    {
//        $behaviors = parent::behaviors();
//        $behaviors['authenticator'] = [
//            'class' => QueryParamAuth::className(),
//            'tokenParam' => 'auth_key',
//            'only' => [
//                'all-for-management',
//                'all-for-city',
//                'all',
//                'all-for-class',
//                'one',
//                'update',
//                'delete',
//            ],
//        ];
//        $behaviors['access'] = [
//            'class' => AccessControl::className(),
//            'only' => [
//                'all-for-management',
//                'all-for-city',
//                'all',
//                'all-for-class',
//                'one',
//                'update',
//                'delete',
//            ],
//            'rules' => [
//                [
//                    'actions' => [
//                        'all-for-management',
//                        'all-for-city',
//                        'all',
//                        'all-for-class',
//                        'one',
//                        'update',
//                        'delete',
//                    ],
//                    'allow' => true,
//                    'roles' => [
//                        'tutor',
//                        'teacher',
//                        'curator',
//                        'manager',
//                        'admin'
//                    ],
//
//                ],
//
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
//
//        $behaviors['verbFilter'] = [
//            'class' => VerbFilter::className(),
//            'actions' => [
//                'all' => ['get'],
//                'all-for-class' => ['get'],
//                'all--for-city' => ['get'],
//                'all-for-management' => ['get'],
//                'one' => ['get'],
//                'create' => ['post'],
//                'update' => ['post'],
//                'delete' => ['delete'],
//            ],
//        ];
//
//        return $behaviors;
//    }

    public function actionTest()
    {
        return Category::findOne(1)->advertisementsBuy;
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

    public function actionOne()
    {
        $user = $this->findModel(Yii::$app->request->get('id'));
        $result = $user->oneFields();
        return $result;
    }

    public function actionMenu()
    {
        $result['buy'] = Advertisement::find()->where(['type' => Advertisement::TYPE_BUY])->count();
        $result['sell'] = Advertisement::find()->where(['type' => Advertisement::TYPE_SELL])->count();
        $result['chat'] = Advertisement::find()->where(['type' => Advertisement::TYPE_CHAT])->count();
        $result['finance'] = Advertisement::find()->where(['type' => Advertisement::TYPE_FINANCE])->count();
        $result['news'] = News::find()->where(['status' => News::STATUS_ACTIVE])->count();
        return $result;
    }

    /**
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new User();
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
                $result = Yii::$app->user->identity->one_fields();
                $result['user']['auth_key'] = Yii::$app->user->identity->getAuthKey();
                return $result;

            } else {
                return ['error' => Yii::t('msg/error', 'Invalid login or password')];
            }
        }
        return ['error' => Yii::t('msg/error', 'Error. Bad request.')];
    }

    /**
     * Updates an existing User model.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate()
    {
        $model = User::findOne(['auth_key' => Yii::$app->request->post('auth_key')]);

        if ($model->load(Yii::$app->request->post()) && $model->saveModel() && $model->checkFiles()) {
            return [
                'user' => $model,
            ];
        }
        return ['errors' => $model->errors];

    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            if ($model->status !== 0) {
                return $model;
            } else {
                throw new NotFoundHttpException('The record was archived.');
            }
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');

        }
    }
}
