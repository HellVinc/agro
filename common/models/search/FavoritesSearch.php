<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Favorites;

/**
 * FavoritesnSearch represents the model behind the search form about `common\models\Favorites`.
 */
class FavoritesSearch extends Favorites
{
    public $size = 100;
    public $sort = [
        'id' => SORT_ASC,
    ];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'object_id', 'status',  'trade_type', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['table'], 'safe'],
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
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Favorites::find();

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

        $query->joinWith('advertisement');

        // grid filtering conditions
        $query->andFilterWhere([
            'favorites.id' => $this->id,
            'object_id' => $this->object_id,
            'favorites.status' => $this->status,
            'favorites.created_at' => $this->created_at,
            'favorites.updated_at' => $this->updated_at,
            'favorites.created_by' => $this->created_by,
            'favorites.updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'table', $this->table])
        ->andFilterWhere(['like', 'advertisement.trade_type', $this->trade_type]);

        return $dataProvider;
    }
}
