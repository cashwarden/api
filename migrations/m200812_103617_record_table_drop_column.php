<?php

use yii\db\Migration;

/**
 * Class m200812_103617_record_table_drop_column
 */
class m200812_103617_record_table_drop_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%record}}', 'trading_behavior');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200812_103617_record_table_drop_column cannot be reverted.\n";
        $this->addColumn(
            '{{%record}}',
            'trading_behavior',
            $this->tinyInteger()->notNull()->after('target_account_id')
        );
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200812_103617_record_table_drop_column cannot be reverted.\n";

        return false;
    }
    */
}
