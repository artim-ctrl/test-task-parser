<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%budget}}`.
 */
class m230419_151834_create_budget_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%budget}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%budget}}');
    }
}
