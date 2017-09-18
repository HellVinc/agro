<?php

namespace api\modules\v1\controllers;

use common\components\UploadModel;
use common\components\traits\errors;
use common\models\Attachment;
use common\models\Category;
use common\models\Comment;
use common\models\Favorites;
use common\models\Log;
use common\models\Message;
use common\models\search\CategorySearch;
use common\models\User;
use Yii;
use common\models\Advertisement;
use common\models\search\AdvertisementSearch;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * AdvertisementController implements the CRUD actions for Advertisement model.
 */
class AdvertisementController extends Controller
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
                'one',
                'update',
                'create',
                'delete',
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
                'one' => ['get'],
                'create' => ['post'],
                'update' => ['post'],
                'delete' => ['post'],
            ],
        ];

        return $behaviors;
    }
//
//    public function actionTest()
//    {
//        $model = Advertisement::findAll(['trade_type' => 2]);
//        return ArrayHelper::toArray($model, [
//            Advertisement::className() => [
//                'id'
//            ]
//        ]);
//    }


    /**
     * Lists all Advertisement models.
     * @return mixed
     */
    public function actionAll()
    {
        $model = new AdvertisementSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get());
        $models = Advertisement::allFields($dataProvider->getModels());
        return [
            'model' => $models,
            'count_model' => $dataProvider->getTotalCount(),
            'page_count' => $dataProvider->pagination->pageCount,
            'page' => $dataProvider->pagination->page + 1,
            'unread_messages' => User::unreadMessages()
        ];
    }

    /**
     * Displays a single Advertisement model.
     * @return mixed
     */
    public function actionOne()
    {
        $model = $this->findModel(Yii::$app->request->get('id'));
        return $model->oneFields();
    }

    /**
     * Creates a new Advertisement model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
//        return $_FILES;
        $model = new Advertisement();

        if ($model->load(Yii::$app->request->post()) && $model->save() && $model->checkFiles() && !$model->getErrors()) {
            $log = new Log();
            $log->object_id = $model->id;
            $log->table = Advertisement::tableName();
            $log->save();
            return $model->oneFields();
        }
        return ['errors' => $model->errors];
    }

    /**
     * Updates an existing Advertisement model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionUpdate()
    {
        $model = $this->findModel(Yii::$app->request->post('id'));

        if ($model->load(Yii::$app->request->post()) && $model->save() && $model->checkFiles() && !$model->getErrors()) {

            return $model->oneFields();
        }
        return ['errors' => $model->errors];
    }

    /**
     * Deletes an existing Advertisement model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionDelete()
    {
        return $this->findModel(Yii::$app->request->post('id'))->delete();
    }

    /**
     * Finds the Advertisement model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Advertisement the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Advertisement::findOne($id)) !== null) {
            if ($model->status !== 0) {
                return $model;
            }
            throw new NotFoundHttpException('The record was archived.');
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

//    public function afterAction($action, $result)
//    {
//        $model = new Log();
//        $model->action_name = $action->id;
//        $model->ctrl_name = Advertisement::tableName();
//        return $model->save();
////        return parent::afterAction($action, $result); // TODO: Change the autogenerated stub
//    }


//    public function beforeAction($action)
//    {
//        $model = new Log();
//        $model->action_name = $action;
//        $model->ctrl_name = Advertisement::className();
//        return $model->save();
//    }
}
