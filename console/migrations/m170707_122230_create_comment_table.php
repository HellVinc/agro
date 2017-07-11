<?php

use yii\db\Migration;

/**
 * Handles the creation of table `comment`.
 */
class m170707_122230_create_comment_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('comment', [
            'id' => $this->primaryKey(),
            'advertisement_id' => $this->integer(),
            'text' => $this->text()->notNull(),
            'viewed' => $this->integer(),
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
        $this->dropTable('comment');
    }
}
