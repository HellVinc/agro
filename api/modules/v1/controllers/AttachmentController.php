<?php

namespace api\modules\v1\controllers;

use common\components\UploadModel;
use common\models\Advertisement;
use Yii;
use common\models\Attachment;
use common\models\search\AttachmentSearch;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * AttachmentController implements the CRUD actions for Attachment model.
 */
class AttachmentController extends Controller
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
                'create-multiple' => ['post'],
                'update' => ['post'],
                'delete' => ['post'],
            ],
        ];

        return $behaviors;
    }

    /**
     * Lists all Attachment models.
     * @return mixed
     */
    public function actionAll()
    {
        $model = new AttachmentSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get());
        return [
            'models' => Attachment::allFields($dataProvider->getModels()),
            'count_model' => $dataProvider->getTotalCount()
        ];
    }

    /**
     * Displays a single Attachment model.
     * @param integer $id
     * @return mixed
     */
    public function actionOne()
    {
        $model = $this->findModel(Yii::$app->request->get('id'));
        return $model->oneFields();
    }

    /**
     * Creates a new Attachment model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $file = new UploadModel(['scenario' => UploadModel::ONE_FILE]);
        $file->imageFile = UploadedFile::getInstanceByName('file');
        $model = new Attachment();
        $model->url = $file->upload(Yii::$app->request->post('object_id'), 'files/' . Yii::$app->request->post('table'));
        $model->extension = $file->imageFile->extension;
        $model->object_id = Yii::$app->request->post('object_id');
        $model->table = Yii::$app->request->post('table');
        if ($model->save()) {
            return $model->oneFields();
        }
        return ['errors' => $model->errors];

    }

    /**
     * Updates an existing Attachment model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionCreateMultiple()
    {
        $file = new UploadModel();
        $file->imageFile = UploadedFile::getInstancesByName('file');
        if($file->upload(Yii::$app->request->post('object_id'), 'files/' . Yii::$app->request->post('table'))){
            return $file;
        }
    }

    /**
     * Deletes an existing Comment model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete()
    {
        return $this->findModel(Yii::$app->request->post('id'))->delete();
    }
    /**
     * Finds the Attachment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Attachment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Attachment::findOne($id)) !== null) {
            if ($model->status !== 0) {
                return $model;
            }
                throw new NotFoundHttpException('The record was archived.');
        }
            throw new NotFoundHttpException('The requested page does not exist.');
    }
}
