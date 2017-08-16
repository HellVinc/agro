<?php

use yii\db\Migration;

/**
 * Handles the creation of table `report`.
 */
class m170808_133849_create_report_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('report', [
            'id' => $this->primaryKey(),
            'object_id' => $this->integer()->notNull(),
            'table' => $this->string(255)->notNull(),
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
        $this->dropTable('report');
    }
}
