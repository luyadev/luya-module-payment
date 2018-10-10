<?php

use yii\db\Migration;

/**
 * Class m181010_091426_order_fields_process_timestamp
 */
class m181010_091426_order_fields_process_timestamp extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('payment_process', 'create_timestamp', $this->integer()->notNull());
        $this->addColumn('payment_process', 'close_timestamp', $this->integer());
        $this->addColumn('payment_process_trace', 'ip', $this->string(45)->notNull());

        $this->createIndex('process_id', 'payment_process_trace', ['process_id']);

        $this->createTable('payment_process_item', [
            'id' => $this->primaryKey(),
            'process_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'qty' => $this->integer(),
            'amount' => $this->integer(),
        ]);

        $this->createIndex('process_id', 'payment_process_item', ['process_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('payment_process', 'create_timestamp');
        $this->dropColumn('payment_process', 'close_timestamp');
        $this->dropColumn('payment_process_trace', 'ip');

        $this->dropTable('payment_process_item');
    }
}
