<?php

use yii\db\Migration;

/**
 * Class m180425_193943_drop_process_fields
 */
class m180425_193943_drop_process_fields extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('payment_process', 'provider_name');
        $this->dropColumn('payment_process', 'transaction_config');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('payment_process', 'provider_name', $this->string());
        $this->addColumn('payment_process', 'transaction_config', $this->text());
    }
}
