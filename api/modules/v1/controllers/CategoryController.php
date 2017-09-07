<?php

namespace api\modules\v1\controllers;

use common\models\User;
use Yii;
use common\models\Category;
use common\models\search\CategorySearch;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends Controller
{

//    public function behaviors()
//    {
//        $behaviors = parent::behaviors();
//        $behaviors['verbFilter'] = [
//            'class' => VerbFilter::className(),
//            'actions' => [
//                'all' => ['get'],
//                'one' => ['get'],
//            ],
//        ];
//        return $behaviors;
//    }

    /**
     * Lists all Category models.
     * @return mixed
     */
    public function actionAll()
    {
        $model = new CategorySearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get());

        return Category::allFields($dataProvider->getModels());
//            'count_model' => $dataProvider->getTotalCount()

//        return $result ? Category::getFields($result['models']) : $model->getErrors();
    }

    /**
     * Displays a single Category model.
     * @return mixed
     */
    public function actionOne($id)
    {
        $model = $this->findModel($id);
        return $model->oneFields();
    }

//    /**
//     * Creates a new Category model.
//     * If creation is successful, the browser will be redirected to the 'view' page.
//     * @return mixed
//     */
//    public function actionCreate()
//    {
//        $model = new Category();
//
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return $model->id;
//        } else {
//            return ['errors' => $model->errors];
//        }
//    }

//    /**
//     * Updates an existing Category model.
//     * If update is successful, the browser will be redirected to the 'view' page.
//     * @param integer $id
//     * @return mixed
//     */
//    public function actionUpdate($id)
//    {
//        $model = $this->findModel($id);
//
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return [
//                'category' => $model,
//            ];
//        } else {
//            return ['errors' => $model->errors()];
//        }
//    }

    /**
     * Deletes an existing Category model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionDelete($id)
    {
        return $this->findModel($id)->delete();
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
            }
            throw new NotFoundHttpException('The record was archived.');
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

