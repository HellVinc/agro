<?php

use yii\db\Migration;

/**
 * Handles the creation of table `rating`.
 */
class m170711_132748_create_rating_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('rating', [
            'id' => $this->primaryKey(),
            'rating' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
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
        $this->dropTable('rating');
    }
}
