<?php

namespace api\modules\v1\controllers;

use Yii;
use common\models\Favorites;
use common\models\search\FavoritesSearch;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * FavoritesController implements the CRUD actions for Favorites model.
 */
class FavoritesController extends Controller
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
                'update',
                'create',
                'delete',
            ],
        ];
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

        $behaviors['verbFilter'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'all' => ['get'],
                'one' => ['get'],
                'create' => ['post'],
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
        $model = new FavoritesSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get());
        return [
            'models' => Favorites::allFields($dataProvider->getModels()),
            'count_model' => $dataProvider->getTotalCount()
        ];
    }

//    /**
//     * Displays a single Favorites model.
//     * @return mixed
//     */
//    public function actionView()
//    {
//        return $this->findModel(Yii::$app->request->get('id'))->oneFields();
//    }

    /**
     * Creates a new Favorites model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Favorites();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return [
                'id' =>  $model->object_id,
                'message' => 'Додано до обраних'
            ];
        }
        return ['errors' => $model->errors];

    }

//    /**
//     * Updates an existing Favorites model.
//     * If update is successful, the browser will be redirected to the 'view' page.
//     * @param integer $id
//     * @return mixed
//     */
//    public function actionUpdate($id)
//    {
//        $model = $this->findModel($id);
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return [
//                strtolower($model->getClassName()) => $model
//            ];
//        }
//        return ['errors' => $model->errors()];
//    }


    /**
     * Deletes an existing Favorites model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        return $this->findModel($id)->delete();
    }

    /**
     * Finds the Favorites model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Favorites the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Favorites::findOne($id)) !== null) {
            if ($model->status !== 0) {
                return $model;
            }
                throw new NotFoundHttpException('The record was archived.');
        }
            throw new NotFoundHttpException('The requested page does not exist.');
    }
}
