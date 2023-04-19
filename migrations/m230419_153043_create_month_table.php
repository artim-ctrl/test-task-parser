<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%month}}`.
 */
class m230419_153043_create_month_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%month}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'month' => $this->string()->notNull(),
            'amount' => $this->float(2),
        ]);

        $this->createIndex('idx-month-product_id', 'month', 'product_id');
        $this->addForeignKey(
            'fk-month-product_id',
            'month',
            'product_id',
            'product',
            'id',
            'CASCADE',
        );

        $this->createIndex('idx-month-month-product_id', 'month', ['month', 'product_id'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%month}}');
    }
}
