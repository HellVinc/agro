<?php

use yii\db\Migration;

/**
 * Handles the creation of table `room`.
 */
class m170808_085944_create_room_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('room', [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer(),
            'title' => $this->string(255)->notNull(),
            'viewed' => $this->smallInteger()->defaultValue(0),
            'text' => $this->text()->notNull(),
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
    public function safeDown()
    {
        $this->dropTable('room');
    }
}
