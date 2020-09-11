<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%recurrence}}`.
 */
class m200909_030624_create_recurrence_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%recurrence}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'frequency' => $this->tinyInteger()->notNull(),
            'interval' => $this->tinyInteger()->defaultValue(1),
            'schedule' => $this->string(),
            'transaction_id' => $this->integer()->notNull(),
            'started_at' => $this->timestamp()->defaultValue(null),
            'execution_date' => $this->timestamp()->defaultValue(null),
            'status' => $this->tinyInteger()->defaultValue(1),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);

        $this->createIndex('record_user_id_transaction_id', '{{%recurrence}}', ['user_id', 'transaction_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%recurrence}}');
    }
}
