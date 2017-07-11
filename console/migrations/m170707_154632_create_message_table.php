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
            'discussion_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'text' => $this->text()->notNull(),
            'checked' => $this->integer()->defaultValue(0)->notNull(),
            'status' => $this->smallInteger(1)->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer()->notNull(),
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
