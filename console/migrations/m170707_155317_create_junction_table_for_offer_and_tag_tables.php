<?php

use yii\db\Migration;

/**
 * Handles the creation of table `offer_tag`.
 * Has foreign keys to the tables:
 *
 * - `offer`
 * - `tag`
 */
class m170707_155317_create_junction_table_for_offer_and_tag_tables extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('offer_tag', [
            'offer_id' => $this->integer(),
            'tag_id' => $this->integer(),
            'PRIMARY KEY(offer_id, tag_id)',
        ]);

        // creates index for column `offer_id`
        $this->createIndex(
            'idx-offer_tag-offer_id',
            'offer_tag',
            'offer_id'
        );

        // add foreign key for table `offer`
        $this->addForeignKey(
            'fk-offer_tag-offer_id',
            'offer_tag',
            'offer_id',
            'offer',
            'id',
            'CASCADE'
        );

        // creates index for column `tag_id`
        $this->createIndex(
            'idx-offer_tag-tag_id',
            'offer_tag',
            'tag_id'
        );

        // add foreign key for table `tag`
        $this->addForeignKey(
            'fk-offer_tag-tag_id',
            'offer_tag',
            'tag_id',
            'tag',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `offer`
        $this->dropForeignKey(
            'fk-offer_tag-offer_id',
            'offer_tag'
        );

        // drops index for column `offer_id`
        $this->dropIndex(
            'idx-offer_tag-offer_id',
            'offer_tag'
        );

        // drops foreign key for table `tag`
        $this->dropForeignKey(
            'fk-offer_tag-tag_id',
            'offer_tag'
        );

        // drops index for column `tag_id`
        $this->dropIndex(
            'idx-offer_tag-tag_id',
            'offer_tag'
        );

        $this->dropTable('offer_tag');
    }
}
