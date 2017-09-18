<?php
namespace common\components\helpers;

use common\models\Log;
use Yii;
use yii\rest\Controller;

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 17.08.2017
 * Time: 15:54
 */

class ExtendedController extends Controller
{

    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {
        $model = new Log();
        $this->action->id;
        $result = parent::afterAction($action, $result);
        return $this->serializeData($result);
    }
}