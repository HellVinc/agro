<?php

use yii\db\Migration;

/**
 * Handles the creation of table `tag`.
 */
class m170707_122346_create_tag_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('tag', [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),
            'status' => $this->smallInteger(1)->defaultValue(10),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        $this->createIndex('unique_category_name', 'tag', ['category_id', 'name'], true);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropIndex('unique_category_name', 'tag');
        $this->dropTable('tag');
    }
}
