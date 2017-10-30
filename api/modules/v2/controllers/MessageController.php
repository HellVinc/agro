<?php

namespace api\modules\v2\controllers;

use common\models\Room;
use Yii;
use common\models\Message;
use common\models\search\MessageSearch;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

/**
 * MessageController implements the CRUD actions for Message model.
 */
class MessageController extends Controller
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
     * Lists all Message models.
     * @return mixed
     */
    public function actionAll()
    {
        $model = new MessageSearch();
        $get = Yii::$app->request->get();
        $dataProvider = $model->searchAll($get, true);
        $ret = Message::allFields($dataProvider);
        $room = false;

        if (!empty($get['room_id'])) {
            $room = Room::findOne($get['room_id']);
        }

        if ($room) {
            $category = $room->category;

            $ret = array_merge([
                'room' => $room->responseOne([
                    'id',
                    'category',
                    'category_id',
                    'title',
                    'text',
                    'viewed',
                    'created_at',
                    'created_by',
                ]),
                'category' => $category->oneFields(),
            ], $ret);
        }
        return $ret;
    }

    /**
     * Creates a new Message model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Message();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model->oneFields();
        }

        return ['errors' => $model->errors];
    }

    /**
     * Updates an existing Message model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionUpdate()
    {
        $id = Yii::$app->request->get('id') ?: Yii::$app->request->post('id');
        $model = $this->findModel($id, true);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model->oneFields();
        }

        return ['errors' => $model->errors()];
    }

    /**
     * Deletes an existing Message model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param $id
     * @return mixed
     * @throws \Exception
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionDelete($id)
    {
        return $this->findModel($id)->delete();
    }

    /**
     * Finds the Message model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param bool $ignoreStatus
     * @return Message the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id, $ignoreStatus = false)
    {
        if (($model = Message::findOne($id)) !== null) {
            if ($ignoreStatus || $model->status !== 0) {
                return $model;
            }
            throw new NotFoundHttpException('The record was archived.');
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
