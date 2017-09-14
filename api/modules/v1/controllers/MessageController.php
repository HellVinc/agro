<?php

namespace api\modules\v1\controllers;

use Yii;
use common\models\Message;
use common\models\search\MessageSearch;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * MessageController implements the CRUD actions for Message model.
 */
class MessageController extends Controller
{

    /**
     * @return array
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
                'delete',
            ],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => [
                'all',
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
                'delete' => ['post'],
            ],
        ];

        return $behaviors;
    }

    /**
     * Lists all Message models.
     * @return mixed
     */
    public function actionAll()
    {
        $model = new MessageSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get());
        $models = Message::allFields($dataProvider->getModels());
        $model->changeViewed($models);

        return [
            'models' => $models,
            'count_model' => $dataProvider->getTotalCount(),
            'page_count' => $dataProvider->pagination->pageCount,
            'page' => $dataProvider->pagination->page + 1
        ];

    }


    /**
     * Creates a new Message model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Message();

        if ($model->load(Yii::$app->request->post()) && $model->save() && $model->checkFiles() && !$model->getErrors()) {

            $context = new \ZMQContext();
            $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, $model->created_by);
            $socket->connect("tcp://localhost:5555");
            $socket->send(json_encode($model->toArray()));
            return $model->oneFields();
        }
        return ['errors' => $model->errors];
    }

    /**
     * Deletes an existing Message model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionDelete()
    {
        return $this->findModel(Yii::$app->request->post('id'))->delete();
    }

    /**
     * Finds the Message model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Message the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Message::findOne($id)) !== null) {
            if ($model->status !== 0) {
                return $model;
            }
            throw new NotFoundHttpException('The record was archived.');
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
