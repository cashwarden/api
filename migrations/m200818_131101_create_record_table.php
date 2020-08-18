<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%record}}`.
 */
class m200818_131101_create_record_table extends Migration
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
            'amount_cent' => $this->integer()->notNull(), // base currency
            'transaction_id' => $this->integer()->notNull(),
            'direction' => $this->tinyInteger()->notNull(),
            'date' => $this->timestamp()->notNull(),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);

        $this->createIndex('record_user_id_transaction_id', '{{%record}}', ['user_id', 'transaction_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%record}}');
    }
}
