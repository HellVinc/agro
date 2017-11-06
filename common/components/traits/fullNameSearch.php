<?php
namespace common\components\traits;

use yii\db\ActiveQuery;

trait fullNameSearch
{
    public $full_name;

    /**
     * Search users by full_name
     *
     * @param ActiveQuery $query
     */
    public function fullNameSearch(ActiveQuery $query)
    {
        if (!empty($this->full_name)) {
            $reverse_text = implode(' ', array_reverse(explode(' ', $this->full_name)));

            $query->andWhere([
                'like',
                'CONCAT(user.first_name, \' \', user.last_name)',
                $this->full_name
            ]);

            if ($reverse_text !== $this->full_name) {
                $query->orWhere([
                    'like',
                    'CONCAT(user.first_name, \' \', user.last_name)',
                    $reverse_text
                ]);
            }
        }
    }
}