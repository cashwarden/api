<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%transaction}}`.
 */
class m200818_130329_create_transaction_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%transaction}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'from_account_id' => $this->integer(),
            'to_account_id' => $this->integer(),
            'type' => $this->tinyInteger()->notNull(),
            'category_id' => $this->integer()->notNull(),
            'amount_cent' => $this->integer()->notNull(), // base currency
            'currency_amount_cent' => $this->integer()->notNull(),
            'currency_code' => $this->string(3)->notNull(),
            'tags' => $this->string()->comment('Multiple choice use,'),
            'description' => $this->string(),
            'remark' => $this->string(),
            'image' => $this->string(),
            'status' => $this->tinyInteger()->defaultValue(1),
            'reimbursement_status' => $this->tinyInteger(),
            'rating' => $this->tinyInteger(),
            'date' => $this->timestamp()->notNull(),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);

        $this->createIndex('transaction_user_id', '{{%transaction}}', 'user_id');

        $this->execute("ALTER TABLE {{%transaction}} ADD FULLTEXT INDEX `full_text` (`description`, `tags`, `remark`)");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%transaction}}');
    }
}
