<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%record}}`.
 */
class m200730_070846_create_record_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%record}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'account_id' => $this->integer()->notNull(),
            'target_account_id' => $this->integer(),
            'trading_behavior' => $this->tinyInteger()->notNull(),
            'category_id' => $this->integer()->notNull(),
            'direction' => $this->tinyInteger()->notNull(),
            'amount_cent' => $this->integer()->notNull(), // base currency
            'currency_amount_cent' => $this->integer()->notNull(),
            'currency_code' => $this->string(3)->notNull(),
            'tags' => $this->string()->comment('Multiple choice use,'),
            'description' => $this->string(),
            'remark' => $this->string(),
            'image' => $this->string(),
            'trading_status' => $this->tinyInteger()->defaultValue(1),
            'reimbursement_status' => $this->tinyInteger(),
            'rating' => $this->tinyInteger(),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);

        $this->createIndex('record_user_id', '{{%record}}', 'user_id');

        $this->execute("ALTER TABLE {{%record}} ADD FULLTEXT INDEX `full_text` (`description`, `tags`, `remark`)");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%record}}');
    }
}
