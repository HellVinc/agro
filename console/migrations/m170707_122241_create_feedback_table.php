<?php

use yii\db\Migration;

/**
 * Handles the creation of table `feedback`.
 */
class m170707_122241_create_feedback_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('feedback', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'message' => $this->text()->notNull(),
            'viewed' => $this->boolean()->defaultValue(false),
            'important' => $this->boolean()->defaultValue(false),
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
        $this->dropTable('feedback');
    }
}
