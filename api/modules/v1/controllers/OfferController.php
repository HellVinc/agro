<?php

namespace api\modules\v1\controllers;

use Yii;
use common\models\Offer;
use common\models\search\OfferSearch;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;
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
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => QueryParamAuth::className(),
            'tokenParam' => 'auth_key',
            'only' => [
                'all',
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
                    'roles' => ['client', 'admin'],
                ],
            ],
        ];

        $behaviors['verbFilter'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'all' => ['get'],
                'update' => ['post'],
                'delete' => ['delete'],
            ],
        ];

        return $behaviors;
    }

    /**
     * Lists all Favorites models.
     * @return mixed
     */
    public function actionAll()
    {
        $model = new OfferSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get());
        return [
            'models' => Offer::allFields($dataProvider->getModels()),
            'count_model' => $dataProvider->getTotalCount()
        ];
    }


    /**
     * Creates a new Offer model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Offer();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model->oneFields();
        }
        return ['errors' => $model->errors];
    }



    /**
     * Finds the Offer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Offer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Offer::findOne($id)) !== null) {
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
