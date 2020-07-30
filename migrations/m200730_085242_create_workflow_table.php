<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%workflow}}`.
 */
class m200730_085242_create_workflow_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
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

        $this->createIndex('workflow_user_id', '{{%workflow}}', 'user_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%workflow}}');
    }
}
