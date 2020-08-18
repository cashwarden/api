<?php

use yii\db\Migration;

/**
 * Class m200818_085852_update_record_table
 */
class m200818_085852_update_record_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%record}}', 'transaction_type', $this->tinyInteger()->notNull()->after('direction'));
        $this->renameColumn('{{%record}}', 'from_account_id', 'account_id');
        $this->dropColumn('{{%record}}', 'to_account_id');
        $this->alterColumn('{{%record}}', 'account_id', $this->integer()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200818_085852_update_record_table cannot be reverted.\n";
        $this->dropColumn('{{%record}}', 'transaction_type');
        $this->renameColumn('{{%record}}', 'account_id', 'from_account_id');
        $this->addColumn('{{%record}}', 'to_account_id', $this->integer()->after('from_account_id'));
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200818_085852_update_record_table cannot be reverted.\n";

        return false;
    }
    */
}
