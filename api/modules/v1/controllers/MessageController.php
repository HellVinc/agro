<?php

namespace api\modules\v1\controllers;

use Yii;
use common\models\Message;
use common\models\search\MessageSearch;
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

//    /**
//     * Displays a single Message model.
//     * @return mixed
//     */
//    public function actionOne()
//    {
//        return $this->findModel(Yii::$app->request->get('id'))->oneFields();
//    }

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
            $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
            $socket->connect("tcp://localhost:5555");

            $socket->send($model->text);
            return $model->oneFields();
        }
        return ['errors' => $model->errors];
    }

//    /**
//     * Updates an existing Message model.
//     * If update is successful, the browser will be redirected to the 'view' page.
//     * @param integer $id
//     * @return mixed
//     */
//    public function actionUpdate($id)
//    {
//        $model = $this->findModel($id);
//
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return $model->oneFields();
//        }
//        return ['errors' => $model->errors()];
//    }

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
