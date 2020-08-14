<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%rule}}`.
 */
class m200814_032606_create_rule_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%workflow}}');

        $this->createTable('{{%rule}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'if_keywords' => $this->string()->notNull()->comment('Multiple choice use,'),
            'if_direction' => $this->tinyInteger()->defaultValue(0)->comment('0:any'),
            'then_direction' => $this->tinyInteger(),
            'then_category_id' => $this->integer(),
            'then_account_id' => $this->integer(),
            'then_transaction_status' => $this->tinyInteger(),
            'then_reimbursement_status' => $this->tinyInteger(),
            'then_tags' => $this->string()->comment('Multiple choice use,'),
            'status' => $this->tinyInteger()->defaultValue(1),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);

        $this->createIndex('rule_user_id', '{{%rule}}', 'user_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%rule}}');

        $this->createTable('{{%workflow}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'trading_behavior' => $this->tinyInteger()->notNull(),
            'pattern' => $this->string()->notNull(),
            'category_id' => $this->integer(),
            'account_id' => $this->integer(),
            'tags' => $this->string()->comment('Multiple choice use,'),
            'status' => $this->tinyInteger()->defaultValue(1),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);
    }
}
