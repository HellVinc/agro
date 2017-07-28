<?php

use yii\db\Migration;

class m970707_152406_relations extends Migration
{
    public function safeUp()
    {
        //table1->table2
        $this->createIndex('category_id', 'tag', 'category_id');
        $this->createIndex('advertisement_id', 'comment', 'advertisement_id');
        $this->createIndex('tag_id', 'advertisement', 'tag_id');
        $this->createIndex('user_id', 'rating', 'user_id');

        $this->addForeignKey('comment_ibfk_1', 'comment', 'advertisement_id', 'advertisement', 'id', 'RESTRICT');
        $this->addForeignKey('advertisement_ibfk_1', 'advertisement', 'tag_id', 'tag', 'id', 'RESTRICT');
        $this->addForeignKey('category_ibfk_1', 'tag', 'category_id', 'category', 'id', 'RESTRICT');
        $this->addForeignKey('user_ibfk_1', 'rating', 'user_id', 'user', 'id', 'RESTRICT');

    }

    public function safeDown()
    {
        $this->dropForeignKey('comment_ibfk_1', 'comment');
        $this->dropForeignKey('advertisement_ibfk_1', 'advertisement');
        $this->dropForeignKey('category_ibfk_1', 'tag');
        $this->dropForeignKey('user_ibfk_1', 'rating');

        $this->dropIndex('category_id', 'tag');
        $this->dropIndex('advertisement_id', 'comment');
        $this->dropIndex('tag_id', 'advertisement');
        $this->dropIndex('user_id', 'rating');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170707_152406_relations cannot be reverted.\n";

        return false;
    }
    */
}
