<?php

namespace common\components\traits;

use yii\debug\models\timeline\DataProvider;

trait findRecords
{
    # search records

    /**
     * @param null $request
     * @param bool $all
     * @return DataProvider
     */
    public function searchAll($request = null, $all = false)
    {
        if ($request && (!$this->load([$this->formName() => $request]) || !$this->validate())) {
            return null;
        }

       //if ($onlyActive || $this->status === null) {
        if (!$all) {
            $this->status = self::STATUS_ACTIVE;
        }

        return $this->search();
    }
}