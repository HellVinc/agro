<?php

namespace api\modules\v2\controllers;

use Yii;
use common\models\News;
use common\models\search\NewsSearch;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;

/**
 * NewsController implements the CRUD actions for News model.
 */
class NewsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                'class' => QueryParamAuth::className(),
                'tokenParam' => 'auth_key',
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
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
     * Lists all News models.
     * @return mixed
     */
    public function actionAll()
    {
        $model = new NewsSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get(), true);

        return News::allFields($dataProvider);
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
            return $model->oneFields();
        } else {
            return ['errors' => $model->errors];
        }
    }

    /**
     * Updates an existing News model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionUpdate()
    {
        $id = Yii::$app->request->get('id') ? Yii::$app->request->get('id') : Yii::$app->request->post('id');

        $model = $this->findModel($id, true);

        if ($model->load(Yii::$app->request->post()) && $model->save() && $model->checkFiles()) {
            return $model->oneFields();
        } else {
            return ['errors' => $model->errors()];
        }
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
     * @param bool $ignoreStatus
     * @return News the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id, $ignoreStatus = false)
    {
        if (($model = News::findOne($id)) !== null) {
            if ($ignoreStatus || $model->status !== 0) {
                return $model;
            }
            throw new NotFoundHttpException('The record was archived.');
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
