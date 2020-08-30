<?php

use yii\db\Migration;

/**
 * Class m200830_030728_create_sort_column
 */
class m200830_030728_create_sort_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{rule}}', 'sort', $this->tinyInteger()->defaultValue(99)->after('status'));
        $this->addColumn('{{account}}', 'sort', $this->tinyInteger()->defaultValue(99)->after('default'));
        $this->addColumn('{{category}}', 'sort', $this->tinyInteger()->defaultValue(99)->after('default'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200830_030728_create_sort_column cannot be reverted.\n";
        $this->dropColumn('{{rule}}', 'sort');
        $this->dropColumn('{{account}}', 'sort');
        $this->dropColumn('{{category}}', 'sort');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200830_030728_create_sort_column cannot be reverted.\n";

        return false;
    }
    */
}
