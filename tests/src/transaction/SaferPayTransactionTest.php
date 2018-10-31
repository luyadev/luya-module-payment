<?php

namespace luya\payment\tests\transaction;

use luya\payment\tests\BasePaymentTestCase;
use luya\payment\transaction\SaferPayTransaction;


class SaferPayTransactionTest extends BasePaymentTestCase
{
    public function testCreate()
    {
        $saferPay = new SaferPayTransaction([
            'accountId' => 123,
        ]);
        $saferPay->setModel($this->generatePayModel());
        $saferPay->setContext($this->generateContextController());
        $r = $saferPay->create();

        $this->assertInstanceOf('yii\web\Response', $r);
    }
}