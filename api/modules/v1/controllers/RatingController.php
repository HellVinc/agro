<?php

namespace api\modules\v1\controllers;

use Yii;
use common\models\Rating;
use common\models\search\RatingSearch;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * RatingController implements the CRUD actions for Rating model.
 */
class RatingController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => QueryParamAuth::className(),
            'tokenParam' => 'auth_key',
            'only' => [
                'all',
                'create',
                'update',
                'delete'
            ],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => [
                'update',
                'create',
                'delete',
            ],
            'rules' => [
                [
                    'actions' => [
                        'create',
                        'update',
                        'delete',
                    ],
                    'allow' => true,
                    'roles' => ['client', 'admin'],
                ],
            ],
        ];

        $behaviors['verbFilter'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'all' => ['get'],
                'create' => ['post'],
                'update' => ['post'],
                'delete' => ['post'],
            ],
        ];

        return $behaviors;
    }

    /**
     * Lists all Rating models.
     * @return mixed
     */
    public function actionAll()
    {
        $model = new RatingSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get());
        return [
            'models' => Rating::allFields($dataProvider->getModels()),
            'count_model' => $dataProvider->getTotalCount()
        ];
    }

    /**
     * Creates a new Rating model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Rating();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model->oneFields();
        }
        return ['errors' => $model->errors];
    }

    /**
     * Updates an existing Rating model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model->id;
        }
        return ['errors' => $model->errors];
    }

    /**
     * Deletes an existing Rating model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionDelete()
    {
        return $this->findModel(Yii::$app->request->post('id'))->delete();
    }

    /**
     * Finds the Rating model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Rating the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Rating::findOne($id)) !== null) {
            if ($model->status !== 0) {
                return $model;
            }
            throw new NotFoundHttpException('The record was archived.');
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
