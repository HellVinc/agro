<?php

namespace api\modules\v2\controllers;

use Yii;
use common\models\Advertisement;
use common\models\search\AdvertisementSearch;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;
/**
 * AdvertisementController implements the CRUD actions for Advertisement model.
 */
class AdvertisementController extends Controller
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
                    //'create' => ['post'],
                    'update' => ['post'],
                    'delete' => ['delete'],
                ],
            ],
        ]);
    }

    /**
     * Lists all Advertisement models.
     * @return mixed
     */
    public function actionAll()
    {
        $model = new AdvertisementSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get(), false);

        return Advertisement::allFields($dataProvider);
    }

    /**
     * Updates an existing Advertisement model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionUpdate()
    {
        $id = Yii::$app->request->get('id') ? Yii::$app->request->get('id') : Yii::$app->request->post('id');

        $model = $this->findModel($id, true);

        if ($model->load(Yii::$app->request->post()) && $model->save() && $model->checkFiles() && !$model->getErrors()) {
            return $model->oneFields();
        } else {
            return ['errors' => $model->errors()];
        }
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

    /**
     * Finds the Advertisement model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param bool $ignoreStatus
     * @return Advertisement the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id, $ignoreStatus = false)
    {
        if (($model = Advertisement::findOne($id)) !== null) {
            if ($ignoreStatus || $model->status !== 0) {
                return $model;
            }
            throw new NotFoundHttpException('The record was archived.');
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
