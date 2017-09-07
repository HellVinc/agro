<?php

use yii\db\Migration;

/**
 * Handles the creation of table `offer`.
 */
class m170707_122321_create_offer_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('offer', [
            'id' => $this->primaryKey(),
            'text' => $this->string(255)->notNull(),
            'viewed' => $this->smallInteger(1)->defaultValue(0),
            'checked' => $this->smallInteger(1)->defaultValue(0),
            'done' => $this->smallInteger(1)->defaultValue(0),
            'status' => $this->smallInteger(1)->defaultValue(10),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('offer');
    }
}
