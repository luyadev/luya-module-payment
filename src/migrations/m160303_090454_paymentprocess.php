<?php

use yii\db\Migration;

class m160303_090454_paymentprocess extends Migration
{
    public function safeUp()
    {
        $this->createTable('payment_process', [
            'id' => $this->primaryKey(),
            'salt' => $this->string(120)->notNull(),
            'hash' => $this->string(120)->notNull()->unique(),
            'random_key' => $this->string(32)->notNull()->unique(),
            'amount' => $this->integer(11)->notNull(), // int value in cents
            'currency' => $this->string(10)->notNull(),
            'order_id' => $this->string(50)->notNull(),
            'provider_name' => $this->string(50)->notNull(),
            'success_link' => $this->string(255)->notNull(),
            'error_link' => $this->string(255)->notNull(),
            'abort_link' => $this->string(255)->notNull(),
            'transaction_config' => $this->text()->notNull(),
            'close_state' => $this->integer(11)->defaultValue(0),
            'is_closed' => $this->boolean()->defaultValue(0),
        ]);
        
        $this->createTable('payment_process_trace', [
            'id' => $this->primaryKey(),
            'process_id' => $this->integer(11)->notNull(),
            'event' => $this->string(255),
            'message' => $this->string(255),
            'timestamp' => $this->integer(11)->notNull(),
            'get' => $this->text(),
            'post' => $this->text(),
            'server' => $this->text(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('payment_process');
        $this->dropTable('payment_process_trace');
    }
}
