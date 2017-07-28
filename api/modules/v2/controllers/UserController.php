<?php

namespace api\modules\v2\controllers;

use common\models\search\UserSearch;
use common\models\User;
use Yii;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;


class UserController extends Controller
{
//    public function behaviors()
//    {
//        $behaviors = parent::behaviors();
//        $behaviors['authenticator'] = [
//            'class' => QueryParamAuth::className(),
//            'tokenParam' => 'auth_key'
//        ];
//        $behaviors['access'] = [
//            'class' => AccessControl::className(),
//            'rules' => [
//                [
//                    'allow' => true,
//                    'roles' => [
//                        'admin'
//                    ],
//                ],
//            ],
//        ];
//
//        $behaviors['verbFilter'] = [
//            'class' => VerbFilter::className(),
//            'actions' => [
//                'all' => ['get'],
//                'update' => ['post'],
//                'delete' => ['delete'],
//            ],
//        ];
//
//        return $behaviors;
//    }

    /**
     * @return array
     */
    public function actionAll()
    {
        $model = new UserSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get());
        return [
            'models' => User::getFields($dataProvider->getModels()),
            'count_model' => $dataProvider->getTotalCount()
        ];
    }

//    public function actionOne()
//    {
//        $user = $this->findModel(Yii::$app->request->get('id'));
//        $result = $user->oneFields();
//        return $result;
//    }

    /**
     * Updates an existing User model.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $post = Yii::$app->request->post();

        if ($post['password'] && $model->validate(['password'])) {
            $model->setPassword($post['password']);
        }

        if ($model->load($post) && $model->saveModel() && $model->checkFiles()) {
            return $model->oneFields();
        }
        return ['errors' => $model->errors];

    }


    /**
     * Deletes an existing Category model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        return $this->findModel($id)->delete();
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
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
