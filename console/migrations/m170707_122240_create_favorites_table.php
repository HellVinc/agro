<?php

use yii\db\Migration;

/**
 * Handles the creation of table `favorites`.
 */
class m170707_122240_create_favorites_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('favorites', [
            'id' => $this->primaryKey(),
            'parent_id' => $this->integer()->notNull(),
            'table' => $this->string()->notNull(),
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
        $this->dropTable('favorites');
    }
}
