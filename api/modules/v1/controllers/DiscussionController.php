<?php

namespace api\modules\v1\controllers;

use Yii;
use common\models\Discussion;
use common\models\search\DiscussionSearch;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * DiscussionController implements the CRUD actions for Discussion model.
 */
class DiscussionController extends Controller
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
     * Lists all Discussion models.
     * @return mixed
     */
    public function actionAll()
    {
        $model = new DiscussionSearch();
        $result = $model->searchAll(Yii::$app->request->get());
        return $result ? $model->allFields($result) : $model->getErrors();

    }

    /**
     * Displays a single Discussion model.
     * @return mixed
     */
    public function actionOne()
    {
        $model = $this->findModel(Yii::$app->request->get('id'));
        return $model->oneFields();
    }

    /**
     * Creates a new Discussion model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Discussion();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return [
                'category' => $model,
            ];
        } else {
            return ['errors' => $model->errors()];
        }
    }

    /**
     * Updates an existing Discussion model.
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
     * Deletes an existing Discussion model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        return $this->findModel($id)->delete(true);
    }

    /**
     * Finds the Discussion model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Discussion the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Discussion::findOne($id)) !== null) {
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
