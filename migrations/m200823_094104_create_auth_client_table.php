<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%auth_client}}`.
 */
class m200823_094104_create_auth_client_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%auth_client}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'type' => $this->tinyInteger()->notNull(),
            'client_id' => $this->string()->notNull(),
            'data' => $this->text(),
            'status' => $this->tinyInteger()->notNull(),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);

        $this->createIndex('login_user_id', '{{%auth_client}}', 'user_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%auth_client}}');
    }
}
