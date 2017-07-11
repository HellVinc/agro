<?phpnamespace common\components\helpers;use Yii;use yii\db\ActiveRecord;/** * Class ExtendedActiveRecord * @package common\components\helpers * * @property string $className */class ExtendedActiveRecord extends ActiveRecord{    const STATUS_ACTIVE = 1;    const STATUS_DELETED = 0;    const FIELD_NAME = 'status';    /**     * @var array validation errors (attribute name => array of errors)     */    private $_errors;    /**     * @param int $newStatus     * @return mixed     */    public function setStatus($newStatus)    {        $name = self::FIELD_NAME;        return $this->$name = $newStatus;    }    /**     * This method set 'status' = STATUS_DELETED     * @param bool $softDelete     * @return bool|false|int     * @throws \Exception     */    public function delete($softDelete = true)    {        if ($softDelete && self::STATUS_ACTIVE) {            $this->setStatus(self::STATUS_DELETED);            return $this->save();        }        return parent::delete();    }    /**     * @return bool||string     */    public function errors()    {        foreach ($this->getErrors() as $error) {            return $error[0];        }        return false;    }    public function getClassName()    {        $namespace = get_class($this);        $name = explode("\\", $namespace);        return end($name);    }}