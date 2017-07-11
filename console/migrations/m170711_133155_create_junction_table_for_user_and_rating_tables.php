<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user_rating`.
 * Has foreign keys to the tables:
 *
 * - `user`
 * - `rating`
 */
class m170711_133155_create_junction_table_for_user_and_rating_tables extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('user_rating', [
            'user_id' => $this->integer(),
            'rating_id' => $this->integer(),
            'PRIMARY KEY(user_id, rating_id)',
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            'idx-user_rating-user_id',
            'user_rating',
            'user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-user_rating-user_id',
            'user_rating',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        // creates index for column `rating_id`
        $this->createIndex(
            'idx-user_rating-rating_id',
            'user_rating',
            'rating_id'
        );

        // add foreign key for table `rating`
        $this->addForeignKey(
            'fk-user_rating-rating_id',
            'user_rating',
            'rating_id',
            'rating',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-user_rating-user_id',
            'user_rating'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            'idx-user_rating-user_id',
            'user_rating'
        );

        // drops foreign key for table `rating`
        $this->dropForeignKey(
            'fk-user_rating-rating_id',
            'user_rating'
        );

        // drops index for column `rating_id`
        $this->dropIndex(
            'idx-user_rating-rating_id',
            'user_rating'
        );

        $this->dropTable('user_rating');
    }
}
