<?php

namespace api\modules\v1\controllers;

use Yii;
use common\models\Comment;
use common\models\search\CommentSearch;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\User;

/**
 * CommentController implements the CRUD actions for Comment model.
 */
class CommentController extends Controller
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
     * Lists all Comment models.
     * @return mixed
     */
    public function actionAll()
    {
        $model = new CommentSearch();
        $result = $model->searchAll(Yii::$app->request->get());
        return $result ? Comment::allFields($result) : $model->getErrors();
    }

    /**
     * Displays a single Comment model.
     * @param integer $id
     * @return mixed
     */
    public function actionOne()
    {
        $model = $this->findModel(Yii::$app->request->get('id'));
        return $model->oneFields();
    }

    /**
     * Creates a new Comment model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Comment();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model->id;
        } else {
            return ['errors' => $model->errors];
        }
    }

    /**
     * Updates an existing Comment model.
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
     * Deletes an existing Comment model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        return $this->findModel($id)->delete(true);

    }

    /**
     * Finds the Comment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Comment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Comment::findOne($id)) !== null) {
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
