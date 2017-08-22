<?php

namespace api\modules\v2\controllers;

use common\models\search\UserSearch;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use common\models\LoginForm;


class UserController extends Controller
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            //'authenticator' => [
            //    'class' => QueryParamAuth::className(),
            //    'tokenParam' => 'auth_key',
            //],
            //'access' => [
            //    'class' => AccessControl::className(),
            //    'rules' => [
            //        [
            //            'allow' => true,
            //            'roles' => ['admin'],
            //        ],
            //    ],
            //],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'all' => ['get'],
                    'update' => ['post'],
                    'delete' => ['delete'],
                ],
            ],
        ]);
    }

    /**
     * @return array
     */
    public function actionAll()
    {
        $model = new UserSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get(), false);

        return User::allFields($dataProvider);
    }

//    public function actionOne()
//    {
//        $user = $this->findModel(Yii::$app->request->get('id'));
//        $result = $user->oneFields();
//        return $result;
//    }

    /**
     * Login admin
     *
     * @return array
     */
    public function actionLogin()
    {
        $model = new LoginForm();

        if (!$model->load(Yii::$app->request->post(), "")) {
            return ['error' => 'Error. Bad request.'];
        }

        if (!$model->login()) {
            return ['error' => 'Invalid login or password'];
        }

        if (Yii::$app->user->identity->role != User::ROLE_ADMIN) {
            Yii::$app->user->logout();
            return ['error' => 'You are not an admin'];
        }

        return Yii::$app->user->identity->responseOne([
            'id',
            'photo',
            'auth_key',
            'first_name',
            'second_name',
        ]);
    }

    /**
     * Updates an existing User model.
     * @return mixed
     */
   public function actionUpdate()
    {
        $id = Yii::$app->request->get('id') ? Yii::$app->request->get('id') : Yii::$app->request->post('id');

        $model = $this->findModel($id, true);
        $post = Yii::$app->request->post();

        if ($post) {
            if (array_key_exists('status', $post)) {
                $model->setStatus($post['status']);
            }

            // if ($post['password'] && $model->validate(['password'])) {
            //     $model->setPassword($post['password']);
            // }

            // if ($post['phone'] && preg_match('/^((?:(?:\+?3)?8)?0)\d{9}$/', $post['phone'])) {
            //     // remove +380
            //     $post['phone'] = (int)preg_replace('/^((?:(?:\+?3)?8)?0)\d{9}$/', '', $post['phone']);
            // }
        }

        if (array_key_exists('status', $post) && $model->save($post)) {//  && $model->saveModel() && $model->checkFiles()
            return $model->oneFields();
        }
        return ['errors' => $model->errors];

    }

    /**
     * Deletes an existing Category model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        return $this->findModel($id)->delete();
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param bool $ignoreStatus
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id, $ignoreStatus = false)
    {
        if (($model = User::findOne($id)) !== null) {
            if ($ignoreStatus || $model->status !== 0) {
                return $model;
            }
            throw new NotFoundHttpException('The record was archived.');
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
