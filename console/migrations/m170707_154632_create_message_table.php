<?php

use yii\db\Migration;

/**
 * Handles the creation of table `message`.
 */
class m170707_154632_create_message_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('message', [
            'id' => $this->primaryKey(),
            'room_id' => $this->integer()->notNull(),
            'text' => $this->text()->notNull(),
            'viewed' => $this->smallInteger()->defaultValue(0),
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
        $this->dropTable('message');
    }
}
