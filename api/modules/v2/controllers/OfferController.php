<?php

namespace api\modules\v2\controllers;

use Yii;
use common\models\Offer;
use common\models\search\OfferSearch;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * OfferController implements the CRUD actions for Offer model.
 */
class OfferController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
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
     * Lists all Category models.
     * @return mixed
     */
    public function actionAll()
    {
        $model = new OfferSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get(), false);

        return [
            'models' => Offer::allFields($dataProvider->getModels()),
            'count_model' => $dataProvider->getTotalCount()
        ];
    }

//    /**
//     * Creates a new Offer model.
//     * If creation is successful, the browser will be redirected to the 'view' page.
//     * @return mixed
//     */
//    public function actionCreate()
//    {
//        $model = new Offer();
//
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return $model->oneFields();
//        } else {
//            return ['errors' => $model->errors];
//        }
//    }

    /**
     * Updates an existing Offer model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id, true);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model->oneFields();
        } else {
            return ['errors' => $model->errors()];
        }
    }


    /**
     * Deletes an existing Offer model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        return $this->findModel($id)->delete();
    }

    /**
     * Finds the Offer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param bool $all
     * @return Offer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id, $all = false)
    {
        if (($model = Offer::findOne($id)) !== null) {
            if ($all || $model->status !== 0) {
                return $model;
            } else {
                throw new NotFoundHttpException('The record was archived.');
            }
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
