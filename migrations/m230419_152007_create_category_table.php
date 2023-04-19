<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%category}}`.
 */
class m230419_152007_create_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%category}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'budget_id' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-category-budget_id', 'category', 'budget_id');
        $this->addForeignKey(
            'fk-category-budget_id',
            'category',
            'budget_id',
            'budget',
            'id',
            'CASCADE',
        );

        $this->createIndex('idx-category-name-budget_id', 'category', ['name', 'budget_id'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%category}}');
    }
}
