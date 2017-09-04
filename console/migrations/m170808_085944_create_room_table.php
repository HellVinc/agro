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
            'description' => $this->text()->notNull(),
            'viewed' => $this->integer(1)->defaultValue(0),
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
