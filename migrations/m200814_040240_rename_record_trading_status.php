<?php

use yii\db\Migration;

/**
 * Class m200814_040240_rename_record_trading_status
 */
class m200814_040240_rename_record_trading_status extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('{{%record}}', 'trading_status', 'transaction_status');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200814_040240_rename_record_trading_status cannot be reverted.\n";
        $this->renameColumn('{{%record}}', 'transaction_status', 'trading_status');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200814_040240_rename_record_trading_status cannot be reverted.\n";

        return false;
    }
    */
}
