<?php

namespace common\components\traits;

use Yii;

trait findRecords
{
    # search records

    public function searchAll($request =  null)
    {
        $this->status = 10;
        if ($request && (!$this->load([soft::lastNameClass(static::className()) => $request]) || !$this->validate())) {
            return false;
        }
        $dataProvider = $this->search();
        $models = $dataProvider->getModels();
        
        return [
            'models' => $models,
//            'count_page' => $dataProvider->pagination->pageCount,
            'count_model' => $dataProvider->getTotalCount()
        ];

    }
}