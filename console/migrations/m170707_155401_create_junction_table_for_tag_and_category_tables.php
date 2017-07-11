<?php

use yii\db\Migration;

/**
 * Handles the creation of table `tag_category`.
 * Has foreign keys to the tables:
 *
 * - `tag`
 * - `category`
 */
class m170707_155401_create_junction_table_for_tag_and_category_tables extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('tag_category', [
            'tag_id' => $this->integer(),
            'category_id' => $this->integer(),
            'PRIMARY KEY(tag_id, category_id)',
        ]);

        // creates index for column `tag_id`
        $this->createIndex(
            'idx-tag_category-tag_id',
            'tag_category',
            'tag_id'
        );

        // add foreign key for table `tag`
        $this->addForeignKey(
            'fk-tag_category-tag_id',
            'tag_category',
            'tag_id',
            'tag',
            'id',
            'CASCADE'
        );

        // creates index for column `category_id`
        $this->createIndex(
            'idx-tag_category-category_id',
            'tag_category',
            'category_id'
        );

        // add foreign key for table `category`
        $this->addForeignKey(
            'fk-tag_category-category_id',
            'tag_category',
            'category_id',
            'category',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `tag`
        $this->dropForeignKey(
            'fk-tag_category-tag_id',
            'tag_category'
        );

        // drops index for column `tag_id`
        $this->dropIndex(
            'idx-tag_category-tag_id',
            'tag_category'
        );

        // drops foreign key for table `category`
        $this->dropForeignKey(
            'fk-tag_category-category_id',
            'tag_category'
        );

        // drops index for column `category_id`
        $this->dropIndex(
            'idx-tag_category-category_id',
            'tag_category'
        );

        $this->dropTable('tag_category');
    }
}
