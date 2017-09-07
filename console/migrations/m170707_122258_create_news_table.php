<?php

use yii\db\Migration;

/**
 * Handles the creation of table `news`.
 */
class m170707_122258_create_news_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('news', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255),
            'text' => $this->text(),
            'url' => $this->text(),
            'type' => $this->integer()->notNull(),
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
        $this->dropTable('news');
    }
}
