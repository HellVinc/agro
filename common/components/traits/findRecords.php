<?php

namespace common\components\traits;

use yii\debug\models\timeline\DataProvider;

trait findRecords
{
    # search records

    /**
     * @param null $request
     * @param bool $onlyActive
     * @return DataProvider
     */
    public function searchAll($request = null, $onlyActive = true)
    {
        if ($request && (!$this->load([$this->formName() => $request]) || !$this->validate())) {
            return null;
        }

        if ($onlyActive || $this->status === null) {
            $this->status = self::STATUS_ACTIVE;
        }

        return $this->search();
    }
}