<?php

use yii\db\Migration;

/**
 * Handles the creation of table `advertisement`.
 */
class m170707_112701_create_advertisement_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('advertisement', [
            'id' => $this->primaryKey(),
            'tag_id' => $this->integer()->notNull(),
            'title' => $this->string(255)->notNull(),
            'text' => $this->text()->notNull(),
            'latitude' => $this->string(32),
            'longitude' => $this->string(32),
            'type' => $this->integer(1),// buy or sell
            'viewed' => $this->smallInteger(1)->defaultValue(0),
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
        $this->dropTable('advertisement');
    }
}
