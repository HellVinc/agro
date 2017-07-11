<?php

use yii\db\Migration;

/**
 * Handles the creation of table `advertisement`.
 */
class m170707_112701_create_advertisement_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('advertisement', [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer()->notNull(),
            'title' => $this->string(255)->notNull(),
            'text' => $this->text()->notNull(),
            'ad_type' => $this->integer(1)->notNull(), // buy or sell
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
        $this->dropTable('advertisement');
    }
}
