<?php

namespace common\models\search;

use common\components\traits\dateSearch;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Room;

/**
 * RoomSearch represents the model behind the search form about `common\models\Room`.
 */
class RoomSearch extends Room
{
    use dateSearch;

    public $size = 10;
    public $sort = [
        'id' => SORT_DESC,
    ];
    public $phone;
    public $full_name;
    public $description;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['size', 'id', 'status', 'viewed', 'category_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['date_from', 'date_to', 'created_from', 'created_to', 'updated_from', 'updated_to'], 'safe'],
            [['title', 'text', 'description', 'phone', 'full_name'], 'safe'],
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
        $query = Room::find();

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

        $query->leftJoin(User::tableName(), 'user.id = room.created_by');

        // grid filtering conditions
        $query->andFilterWhere([
            'room.id' => $this->id,
            'room.category_id' => $this->category_id,
            'room.status' => $this->status,
            'room.created_at' => $this->created_at,
            'room.updated_at' => $this->updated_at,
            'room.created_by' => $this->created_by,
            'room.updated_by' => $this->updated_by,
            'room.viewed' => $this->viewed,
        ]);

        $query
            ->andFilterWhere(['like', 'user.phone', $this->phone])
            ->andFilterWhere(['like', 'CONCAT(user.first_name, \' \', user.last_name)', $this->full_name]);

        if (Yii::$app->controller->module->id === 'v1') {
            if ($this->category_id == 3) {
                $query->andFilterWhere(['room.category_id' => 3]);
            } else {
                $query->andFilterWhere(['not in', 'room.category_id', 3]);
            }
        }

        $query->andFilterWhere(['like', 'room.title', $this->title])
            ->andFilterWhere(['like', 'room.text', $this->text])
            ->andFilterWhere(['like', 'room.description', $this->description]);


        $this->initDateSearch($query);

        return $dataProvider;
    }
}
