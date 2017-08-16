<?php

namespace api\modules\v1\controllers;

use Yii;
use common\models\News;
use common\models\search\NewsSearch;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * NewsController implements the CRUD actions for News model.
 */
class NewsController extends Controller
{
    /**
     * @inheritdoc
     */
//    public function behaviors()
//    {
//        $behaviors = parent::behaviors();
//        $behaviors['authenticator'] = [
//            'class' => QueryParamAuth::className(),
//            'tokenParam' => 'auth_key',
//            'only' => [
//                'update',
//                'create',
//                'delete',
//            ],
//        ];
//        $behaviors['access'] = [
//            'class' => AccessControl::className(),
//            'only' => [
//                'create',
//                'update',
//                'delete',
//            ],
//            'rules' => [
//                [
//                    'actions' => [
//                        'create',
//                        'update',
//                        'delete',
//                    ],
//                    'allow' => true,
//                    'roles' => ['@'],
//
//                ],
//            ],
//        ];
//
//        $behaviors['verbFilter'] = [
//            'class' => VerbFilter::className(),
//            'actions' => [
//                'all' => ['get'],
//                'one' => ['get'],
//                'create' => ['post'],
//                'update' => ['post'],
//                'delete' => ['delete'],
//            ],
//        ];
//
//        return $behaviors;
//    }

    /**
     * Lists all News models.
     * @return mixed
     */
    public function actionAll()
    {
        $model = new NewsSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get());
        return [
            'models' => News::allFields($dataProvider->getModels()),
            'count_model' => $dataProvider->getTotalCount()
        ];
    }

    /**
     * Displays a single News model.
     * @return mixed
     */
    public function actionOne()
    {
        return $this->findModel(Yii::$app->request->get('id'))->oneFields();
    }

    /**
     * Creates a new News model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new News();

        if ($model->load(Yii::$app->request->post()) && $model->save() && $model->checkFiles()) {
            return $model->id;
        }
        return ['errors' => $model->errors];
    }

    /**
     * Updates an existing News model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return [
                strtolower($model->getClassName()) => $model,
            ];
        }
        return ['errors' => $model->errors()];
    }

    /**
     * Deletes an existing News model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        return $this->findModel($id)->delete();
    }

    /**
     * Finds the News model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return News the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = News::findOne($id)) !== null) {
            if ($model->status !== 0) {
                return $model;
            }
            throw new NotFoundHttpException('The record was archived.');
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
