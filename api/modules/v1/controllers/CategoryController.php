<?php

namespace api\modules\v1\controllers;

use common\models\User;
use Yii;
use common\models\Category;
use common\models\search\CategorySearch;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends Controller
{
    /**
     * @inheritdoc
     */
//    public function behaviors()
//    {
//        return [
//            'verbs' => [
//                'class' => VerbFilter::className(),
//                'actions' => [
//                    'delete' => ['POST'],
//                ],
//            ],
//        ];
//    }

    /**
     * Lists all Category models.
     * @return mixed
     */
    public function actionAll()
    {
        $model = new CategorySearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get());

        return [
            'models' => Category::allFields($dataProvider->getModels()),
            'count_model' => $dataProvider->getTotalCount()
        ];

//        return $result ? Category::getFields($result['models']) : $model->getErrors();
    }

    /**
     * Displays a single Category model.
     * @return mixed
     */
    public function actionOne()
    {
        $model = $this->findModel(Yii::$app->request->get('id'));
        return $model->oneFields();
    }

    /**
     * Creates a new Category model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Category();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model->id;
        } else {
            return ['errors' => $model->errors];
        }
    }

    /**
     * Updates an existing Category model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return [
                'category' => $model,
            ];
        } else {
            return ['errors' => $model->errors()];
        }
    }

    /**
     * Deletes an existing Category model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionDelete($id)
    {
        return $this->findModel($id)->delete(true);
    }

    /**
     * Finds the Category model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Category the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Category::findOne($id)) !== null) {
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
