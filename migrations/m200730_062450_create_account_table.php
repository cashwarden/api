<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%account}}`.
 */
class m200730_062450_create_account_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%account}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->string(120)->notNull(),
            'type' => $this->tinyInteger()->notNull(),
            'color' => $this->string(7)->notNull(),
            'balance_cent' => $this->bigInteger()->defaultValue(0),
            'currency_code' => $this->string(3)->notNull(),
            'status' => $this->tinyInteger()->defaultValue(1),
            'exclude_from_stats' => $this->tinyInteger()->defaultValue(0),
            'credit_card_limit' => $this->integer(),
            'credit_card_repayment_day' => $this->tinyInteger(),
            'credit_card_billing_day' => $this->tinyInteger(),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);

        $this->createIndex('account_user_id', '{{%account}}', 'user_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%account}}');
    }

}
