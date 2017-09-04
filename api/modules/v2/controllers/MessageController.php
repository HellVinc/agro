<?php

namespace api\modules\v2\controllers;

use Yii;
use common\models\Message;
use common\models\search\MessageSearch;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

/**
 * MessageController implements the CRUD actions for Message model.
 */
class MessageController extends Controller
{
    /**
     * @inheritdoc
     */
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
                    'create' => ['post'],
                    'update' => ['post'],
                    'delete' => ['delete'],
                ],
            ],
        ]);
    }

    /**
     * Lists all Message models.
     * @return mixed
     */
    public function actionAll()
    {
        $model = new MessageSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get(), true);
        return Message::allFields($dataProvider);
    }

//    /**
//     * Creates a new Message model.
//     * If creation is successful, the browser will be redirected to the 'view' page.
//     * @return mixed
//     */
    public function actionCreate()
    {
        $model = new Message();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model->oneFields();
        } else {
            return ['errors' => $model->errors];
        }
    }

    /**
     * Updates an existing Message model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model->oneFields();
        }

        return ['errors' => $model->errors()];
    }

    /**
     * Deletes an existing Message model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        return $this->findModel($id)->delete();
    }

    /**
     * Finds the Message model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param bool $ignoreStatus
     * @return Message the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id, $ignoreStatus = false)
    {
        if (($model = Message::findOne($id)) !== null) {
            if ($ignoreStatus || $model->status !== 0) {
                return $model;
            }
            throw new NotFoundHttpException('The record was archived.');
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
