<?php

use yii\db\Migration;

/**
 * Class m200818_021303_update_rules_table
 */
class m200818_021303_update_rules_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('{{%rule}}', 'then_account_id', 'then_from_account_id');
        $this->addColumn('{{%rule}}', 'then_to_account_id', $this->integer()->after('then_from_account_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200818_021303_update_rules_table cannot be reverted.\n";
        $this->renameColumn('{{%rule}}', 'then_from_account_id', 'then_account_id');
        $this->dropColumn('{{%rule}}', 'then_to_account_id');
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200818_021303_update_rules_table cannot be reverted.\n";

        return false;
    }
    */
}
