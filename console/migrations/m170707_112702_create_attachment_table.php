<?php

use yii\db\Migration;

/**
 * Handles the creation of table `attachment`.
 */
class m170707_112702_create_attachment_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('attachment', [
            'id' => $this->primaryKey(),
            'parent_id' => $this->integer()->notNull(),
            'table' => $this->string(255)->notNull(),
            'extension' => $this->string(255)->notNull(),
            'url' => $this->string(255)->notNull(),
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
        $this->dropTable('attachment');
    }
}
