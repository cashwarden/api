<?php

use yii\db\Migration;

/**
 * Class m200816_001202_update_record_table
 */
class m200816_001202_update_record_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%record}}', 'date', $this->timestamp()->notNull()->after('rating'));
        $this->renameColumn('{{%record}}', 'account_id', 'from_account_id');
        $this->renameColumn('{{%record}}', 'target_account_id', 'to_account_id');
        $this->alterColumn('{{%record}}', 'from_account_id', $this->integer());

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200816_001202_update_record_table cannot be reverted.\n";

        $this->dropColumn('{{%record}}', 'date');
        $this->renameColumn('{{%record}}', 'from_account_id', 'account_id');
        $this->renameColumn('{{%record}}', 'to_account_id', 'target_account_id');
        $this->alterColumn('{{%record}}', 'account_id', $this->integer()->notNull());

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200816_001202_update_record_table cannot be reverted.\n";

        return false;
    }
    */
}
