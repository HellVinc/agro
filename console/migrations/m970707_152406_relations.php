<?php

use yii\db\Migration;

class m970707_152406_relations extends Migration
{
    public function safeUp()
    {
        //table1->table2
        $this->createIndex('category_id', 'advertisement', 'category_id');
        $this->createIndex('advertisement_id', 'comment', 'advertisement_id');
        $this->createIndex('category_id', 'discussion', 'category_id');
        $this->createIndex('discussion_id', 'message', 'discussion_id');

        $this->addForeignKey('advertisement_ibfk_1', 'advertisement', 'category_id', 'category', 'id', 'CASCADE');
        $this->addForeignKey('comment_ibfk_1', 'comment', 'advertisement_id', 'advertisement', 'id', 'CASCADE');
        $this->addForeignKey('discussion_ibfk_1', 'discussion', 'category_id', 'category', 'id', 'CASCADE');
        $this->addForeignKey('message_ibfk_1', 'message', 'discussion_id', 'discussion', 'id', 'CASCADE');

    }

    public function safeDown()
    {
        echo "m170707_152406_relations cannot be reverted.\n";

        return false;
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
