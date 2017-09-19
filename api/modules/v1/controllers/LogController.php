<?php

namespace api\modules\v1\controllers;

use Yii;
use common\models\Log;
use common\models\search\LogSearch;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * LogController implements the CRUD actions for Log model.
 */
class LogController extends Controller
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
                'all'
            ],
        ];

        $behaviors['verbFilter'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'all' => ['get'],
            ],
        ];

        return $behaviors;
    }

    /**
     * @return string
     */
    public function actionAll()
    {
        $model = new LogSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get());
        $res = array();
        foreach (Log::allFields($dataProvider->getModels()) as $model){
            if($model['Message']['id'] !== 'DELETED'){
                $res[] = $model;
            }
        }
        return [
            'models' => $res,
            'count_model' => $dataProvider->getTotalCount()
        ];
    }


    /**
     * Finds the Log model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Log the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Log::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The record was archived.');
    }

}
