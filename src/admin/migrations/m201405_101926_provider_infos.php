<?php

use yii\db\Migration;

/**
 * Class m201405_101926_provider_infos
 */
class m201405_101926_provider_infos extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('payment_process', 'provider_data', $this->text());;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('payment_process', 'provider_data');;
    }
}
