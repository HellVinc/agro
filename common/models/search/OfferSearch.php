<?php

namespace common\models\search;

use common\components\traits\dateSearch;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Offer;

/**
 * OfferSearch represents the model behind the search form about `common\models\Offer`.
 */
class OfferSearch extends Offer
{
    use dateSearch;

    public $size = 10;
    public $sort = [
        'id' => SORT_DESC,
    ];
    public $description;
    public $phone;
    public $first_name;
    public $last_name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['date_from', 'date_to', 'created_from', 'created_to', 'updated_from', 'updated_to'], 'safe'],
            [['text', 'description', 'phone', 'first_name', 'last_name'], 'safe'], // text = description
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
        $query = Offer::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
//                'pageSize' => $this->size,
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

        $query->leftJoin(User::tableName(), 'user.id = offer.created_by');

        // grid filtering conditions
        $query->andFilterWhere([
            'offer.id' => $this->id,
            'offer.status' => $this->status,
            'offer.created_at' => $this->created_at,
            'offer.updated_at' => $this->updated_at,
            'offer.created_by' => $this->created_by,
            'offer.updated_by' => $this->updated_by,
        ]);

        $query
            ->andFilterWhere(['like', 'user.phone', $this->phone])
            ->andFilterWhere(['like', 'user.first_name', $this->first_name])
            ->andFilterWhere(['like', 'user.last_name', $this->last_name]);

        $query->andFilterWhere(['or',
            ['like', 'text', $this->text],
            ['like', 'text', $this->description],
        ]);

        $this->initDateSearch($query);
        return $dataProvider;
    }
}
