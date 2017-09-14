<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\User;

/**
 * UserSearch represents the model behind the search form about `common\models\User`.
 */
class UserSearch extends User
{
    public $size = 10;
    public $sort = [
        'id' => SORT_ASC,
    ];
    public $count_reports;
    public $blocked;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'phone', 'status', 'count_reports', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['first_name', 'middle_name', 'last_name'], 'safe'],
            [['count_reports', 'blocked'], 'in', 'range' => [0,1]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = User::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this->size,
            ],
            'sort' => [
                'defaultOrder' => $this->sort
            ],
        ]);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->addSelect('user.*, COUNT(report.id) AS count_reports')->from(self::tableName());
        $query->leftJoin('report', 'report.object_id = user.id AND report.status = 10 AND report.table = "user"');
        $query->addGroupBy('user.id');

        if (null !== $this->blocked) {
            $query->andFilterWhere([$this->blocked ? '=' : '!=', 'user.role', self::ROLE_CLIENT_BLOCKED]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'user.id' => $this->id,
            'phone' => $this->phone,
            'user.status' => $this->status,
            'user.created_at' => $this->created_at,
            'user.updated_at' => $this->updated_at,
            'user.created_by' => $this->created_by,
            'user.updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'first_name', $this->first_name])
            ->andFilterWhere(['like', 'middle_name', $this->middle_name])
            ->andFilterWhere(['like', 'last_name', $this->last_name]);

        if (null !== $this->count_reports) {
            $query->having([$this->count_reports == 0 ? '=' : '>', 'count_reports', '0']);
        }

        return $dataProvider;
    }
}
