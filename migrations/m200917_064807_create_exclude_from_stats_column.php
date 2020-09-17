<?php

use yii\db\Migration;

/**
 * Class m200917_064807_create_exclude_from_stats_column
 */
class m200917_064807_create_exclude_from_stats_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%record}}', 'exclude_from_stats', $this->tinyInteger()->defaultValue(0)->after('source'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200917_064807_create_exclude_from_stats_column cannot be reverted.\n";
        $this->dropColumn('{{%record}}', 'exclude_from_stats');
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200917_064807_create_exclude_from_stats_column cannot be reverted.\n";

        return false;
    }
    */
}
