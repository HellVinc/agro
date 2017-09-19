<?php

namespace common\models\search;

use common\components\traits\dateSearch;
use common\components\traits\deteHelper;
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
    use deteHelper;
    use dateSearch;

    public $size = 10;
    public $sort = [
        'id' => SORT_DESC,
    ];
    public $phone;
    public $count_reports;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'phone', 'closed', 'size', 'tag_id', 'trade_type', 'viewed', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by', 'category_id'], 'integer'],
            [['title', 'city', 'text', 'latitude', 'longitude'], 'safe'],
            [['date_from', 'date_to', 'created_from', 'created_to', 'updated_from', 'updated_to'], 'safe'],
            [['count_reports'], 'in', 'range' => [0,1]],
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

        if($this->size || $this->page) {
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => $this->size,
                ],
                'sort' => [
                    'defaultOrder' => $this->sort
                ],
            ]);
        }

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        if (!empty($this->phone)) {
            $user = User::findOne(['phone' => $this->phone]);
            if (!$user) {
                $query->where('0=1');
                return $dataProvider;
            }
            $this->created_by = $user->id;
        }

        $query->joinWith(Tag::tableName());

        $query->addSelect('advertisement.*, COUNT(report.id) AS count_reports')->from(self::tableName());
        $query->leftJoin( 'report', 'report.object_id = advertisement.id AND report.status = 10 AND report.table = "advertisement"');
        $query->addGroupBy('advertisement.id');

        // grid filtering conditions
        $query->andFilterWhere([
            'advertisement.id' => $this->id,
            'tag_id' => $this->tag_id,
            'city' => $this->city,
            'trade_type' => $this->trade_type,
            'advertisement.viewed' => $this->viewed,
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

        if (!empty($this->count_reports)) {
            $query->having([$this->count_reports == 0 ? '=' : '>', 'count_reports', '0']);
        }

        $this->initDateSearch($query);
        return $dataProvider;
    }
}
