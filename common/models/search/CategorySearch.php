<?php

namespace common\models\search;

//use common\components\traits\dateSearch;
//use common\components\traits\deteHelper;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Category;

/**
 * CategorySearch represents the model behind the search form about `common\models\Category`.
 */
class CategorySearch extends Category
{

//    use deteHelper;
//    use dateSearch;

    public $size = 10;
    public $sort = [
        'id' => SORT_ASC,
    ];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'category_type', 'status', 'created_by', 'updated_by'], 'integer'],
            [['date_from', 'date_to', 'name'], 'safe'],
            //[['date_from', 'date_to', 'created_from', 'created_to', 'updated_from', 'updated_to'], 'string'],
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
        $query = Category::find();

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

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'category_type' => $this->category_type,
            'status' => $this->status,
            //'created_at' => $this->created_at,
            //'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        //$this->initDateSearch($query);

        return $dataProvider;
    }
}
