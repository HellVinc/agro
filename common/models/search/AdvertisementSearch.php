<?php

namespace common\models\search;

use common\models\Tag;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Advertisement;
use common\models\User;

/**
 * AdvertisementSearch represents the model behind the search form about `common\models\Advertisement`.
 */
class AdvertisementSearch extends Advertisement
{
    public $size = 10;
    public $sort = [
        'id' => SORT_DESC,
    ];
    public $phone;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'phone', 'closed', 'size', 'tag_id', 'trade_type', 'viewed', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by', 'category_id'], 'integer'],
            [['title', 'text', 'latitude', 'longitude'], 'safe'],
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
        $query = Advertisement::find();

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

        if ($this->phone) {
            $this->created_by = User::findOne(['phone' => $this->phone])->id;
        }

        $query->joinWith('tag');
        // grid filtering conditions
        $query->andFilterWhere([
            'advertisement.id' => $this->id,
            'tag_id' => $this->tag_id,
            'trade_type' => $this->trade_type,
            'closed' => $this->closed,
            'advertisement.status' => $this->status,
            'advertisement.created_at' => $this->created_at,
            'advertisement.updated_at' => $this->updated_at,
            'advertisement.created_by' => $this->created_by,
            'advertisement.updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'text', $this->text])
            ->andFilterWhere(['like', 'latitude', $this->latitude])
            ->andFilterWhere(['like', 'longitude', $this->longitude])
            ->andFilterWhere(['like', 'tag.category_id', $this->category_id]);

        return $dataProvider;
    }
}
