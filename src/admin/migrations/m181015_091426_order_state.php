<?php

use yii\db\Migration;

/**
 * Class m181010_091426_order_fields_process_timestamp
 */
class m181015_091426_order_state extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('payment_process', 'state_create', $this->boolean()->defaultValue(false));
        $this->addColumn('payment_process', 'state_back', $this->boolean()->defaultValue(false));
        $this->addColumn('payment_process', 'state_fail', $this->boolean()->defaultValue(false));
        $this->addColumn('payment_process', 'state_abort', $this->boolean()->defaultValue(false));
        $this->addColumn('payment_process', 'state_notify', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('payment_process', 'state_create');
        $this->dropColumn('payment_process', 'state_back');
        $this->dropColumn('payment_process', 'state_fail');
        $this->dropColumn('payment_process', 'state_abort');
        $this->dropColumn('payment_process', 'state_notify');
    }
}
