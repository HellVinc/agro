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
            'viewed' => $this->integer()->notNull()->defaultValue(0),
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
        $this->dropTable('comment');
    }
}
