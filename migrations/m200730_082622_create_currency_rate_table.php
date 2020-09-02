<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%currency_rate}}`.
 */
class m200730_082622_create_currency_rate_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%currency_rate}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'currency_code' => $this->string(3)->notNull(),
            'currency_name' => $this->string(60)->notNull(),
            'rate' => $this->bigInteger(),
            'status' => $this->tinyInteger()->defaultValue(1),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);

        $this->createIndex('currency_rate_user_id', '{{%currency_rate}}', 'user_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%currency_rate}}');
    }
}
