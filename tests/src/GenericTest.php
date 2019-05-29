<?php

namespace luya\payment\tests;

use Yii;
use luya\testsuite\traits\MessageFileCompareTrait;
use luya\testsuite\traits\MigrationFileCheckTrait;

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
