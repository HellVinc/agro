<?php

namespace api\modules\v1\controllers;

use common\models\Log;
use Yii;
use common\models\Favorites;
use common\models\search\FavoritesSearch;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;
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
                'create',
            ],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => [
                'create'
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
                'create' => ['post'],
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

    /**
     * Creates a new Favorites model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Favorites();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $log = new Log();
            $log->object_id = $model->id;
            $log->table = Favorites::tableName();
            $log->save();
            return [
                'id' => $model->object_id,
                'message' => 'Додано до обраних'
            ];
        }
        return ['message' => 'Вже було додано до обраних'];

    }

//    /**
//     * Deletes an existing Favorites model.
//     * If deletion is successful, the browser will be redirected to the 'index' page.
//     * @return mixed
//     */
//    public function actionDelete()
//    {
//        $this->findModel(Yii::$app->request->post('id'))->delete();
//        return $this->actionAll();
//    }

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
