<?php

namespace api\modules\v1\controllers;

use Yii;
use common\models\Advertisement;
use common\models\search\AdvertisementSearch;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * AdvertisementController implements the CRUD actions for Advertisement model.
 */
class AdvertisementController extends Controller
{
    /**
     * @inheritdoc
     */
//    public function behaviors()
//    {
//        return [
//            'verbs' => [
//                'class' => VerbFilter::className(),
//                'actions' => [
//                    'delete' => ['POST'],
//                ],
//            ],
//        ];
//    }

    /**
     * Lists all Advertisement models.
     * @return mixed
     */
    public function actionAll()
    {
        $model = new AdvertisementSearch();
        $result = $model->searchAll(Yii::$app->request->get());
        return $result ? Advertisement::allFields($result) : $model->getErrors();
    }

    /**
     * Displays a single Advertisement model.
     * @return mixed
     */
    public function actionOne()
    {
        $model = $this->findModel(Yii::$app->request->get('id'));
        return $model->oneFields();
    }

    /**
     * Creates a new Advertisement model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
//        return $_FILES;
        $model = new Advertisement();

        if ($model->load(Yii::$app->request->post()) && $model->save() && $model->checkFiles() && !$model->getErrors()) {
            return $model->id;
        } else {
            return ['errors' => $model->errors];
        }
    }

    /**
     * Updates an existing Advertisement model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return [
                'category' => $model,
            ];
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
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Advertisement model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Advertisement the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Advertisement::findOne($id)) !== null) {
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
