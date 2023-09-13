<?php

namespace luya\payment\tests;

use luya\testsuite\traits\MessageFileCompareTrait;
use luya\testsuite\traits\MigrationFileCheckTrait;
use Yii;

class GenericTest extends BasePaymentTestCase
{
    use MessageFileCompareTrait;
    use MigrationFileCheckTrait;

    public function testMessages()
    {
        $this->compareMessages(Yii::getAlias('@payment/messages'), 'en');
    }

    public function testMigrations()
    {
        $this->checkMigrationFolder('@paymentadmin/migrations');
    }
}
