<?php

namespace luya\payment\tests\transaction;

use luya\payment\PaymentException;
use luya\payment\tests\BasePaymentTestCase;
use luya\payment\transactions\SaferPayLegacyTransaction;

class SaferPayLegacyTransactionTest extends BasePaymentTestCase
{
    public function testCreate()
    {
        $saferPay = new SaferPayLegacyTransaction([
            'accountId' => 123,
        ]);
        $saferPay->setModel($this->generatePayModel());
        $saferPay->setContext($this->generateContextController());

        $this->expectException(PaymentException::class);
        $r = $saferPay->create();
    }
}
