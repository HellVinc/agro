<?php

namespace api\modules\v1\controllers;

use common\components\UploadModel;
use common\components\traits\errors;
use common\models\Attachment;
use common\models\Category;
use common\models\search\CategorySearch;
use Yii;
use common\models\Advertisement;
use common\models\search\AdvertisementSearch;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;
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

    public function actionTest()
    {
        $model = new UploadModel();
        if (Yii::$app->request->isPost) {
            $model->imageFiles = UploadedFile::getInstancesByName('files');
           if($model->upload()){
               return true;
           }
        }
        return $model->errors;
    }


    /**
     * Lists all Advertisement models.
     * @return mixed
     */
    public function actionAll()
    {
        $model = new AdvertisementSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get());
        return [
            'models' => Advertisement::allFields($dataProvider->getModels()),
            'count_model' => $dataProvider->getTotalCount(),
            'page_count' => $dataProvider->pagination->pageCount,
            'current_page' => $dataProvider->pagination->page
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
            return $model->id;
        }
        return ['errors' => $model->errors];
    }

    /**
     * Updates an existing Advertisement model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save() && !$model->getErrors()) {
            $fileCount = Attachment::find()->where(['object_id' => $model->id, 'table' => 'advertisement'])->count();
            if ($fileCount > 3) {
                return ['errors' => 'You can upload not more then 3 files'];
            }
            $model->checkFiles();
            return [
                strtolower($model->getClassName()) => $model
            ];
        }
        return ['errors' => $model->errors];
    }

    /**
     * Deletes an existing Advertisement model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        return $this->findModel($id)->delete();
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
}
