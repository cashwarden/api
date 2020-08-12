<?php

use yii\db\Migration;

/**
 * Class m200812_090059_add_default_column
 */
class m200812_090059_add_default_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%category}}',
            'default',
            $this->tinyInteger()->defaultValue(0)->after('status')
        );

        $this->addColumn(
            '{{%account}}',
            'default',
            $this->tinyInteger()->defaultValue(0)->after('credit_card_billing_day')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200812_090059_add_default_column cannot be reverted.\n";
        $this->dropColumn('{{%category}}', 'default');
        $this->dropColumn('{{%account}}', 'default');
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200812_090059_add_default_column cannot be reverted.\n";

        return false;
    }
    */
}
