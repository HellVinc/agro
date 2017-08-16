<?php

namespace api\modules\v1\controllers;

use Yii;
use common\models\Feedback;
use common\models\search\FeedbackSearch;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * FeedbackController implements the CRUD actions for Feedback model.
 */
class FeedbackController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => QueryParamAuth::className(),
            'tokenParam' => 'auth_key',
            'only' => [
                'create',
            ],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => [
                'create',
            ],
            'rules' => [
                [
                    'actions' => [
                        'create',
                    ],
                    'allow' => true,
                    'roles' => ['@'],

                ],
            ],
        ];

        $behaviors['verbFilter'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'create' => ['post'],
            ],
        ];

        return $behaviors;
    }


    /**
     * Creates a new Feedback model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Feedback();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model->id;
        } else {
            return ['errors' => $model->errors];
        }
    }


//    /**
//     * Updates an existing Feedback model.
//     * If update is successful, the browser will be redirected to the 'view' page.
//     * @param integer $id
//     * @return mixed
//     */
//    public function actionUpdate($id)
//    {
//        $model = $this->findModel($id);
//
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return [
//                'category' => $model,
//            ];
//        } else {
//            return ['errors' => $model->errors()];
//        }
//    }
//
//    /**
//     * Deletes an existing Feedback model.
//     * If deletion is successful, the browser will be redirected to the 'index' page.
//     * @param integer $id
//     * @return mixed
//     */
//    public function actionDelete($id)
//    {
//        return $this->findModel($id)->delete(true);
//    }

    /**
     * Finds the Feedback model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Feedback the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Feedback::findOne($id)) !== null) {
            if ($model->status !== 0) {
                return $model;
            }
                throw new NotFoundHttpException('The record was archived.');
            }
            throw new NotFoundHttpException('The requested page does not exist.');
    }
}
