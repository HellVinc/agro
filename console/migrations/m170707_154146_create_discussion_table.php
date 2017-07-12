<?php

use yii\db\Migration;

/**
 * Handles the creation of table `discussion`.
 */
class m170707_154146_create_discussion_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('discussion', [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer()->notNull(),
            'tag_id' => $this->integer()->notNull(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text()->notNull(),
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
        $this->dropTable('discussion');
    }
}
