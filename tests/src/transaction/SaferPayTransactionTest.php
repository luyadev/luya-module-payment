<?php

namespace luya\payment\tests\transaction;

use luya\payment\PaymentException;
use luya\payment\tests\BasePaymentTestCase;
use luya\payment\transactions\SaferPayTransaction;
use yii\base\InvalidConfigException;

class SaferPayTransactionTest extends BasePaymentTestCase
{
    public function testConfigException()
    {
        $this->expectException(InvalidConfigException::class);
        new SaferPayTransaction();
    }

    public function testCreate()
    {
        $saferPay = new SaferPayTransaction([
            'terminalId' => 1,
            'customerId' => 1,
            'username' => 'foo',
            'password' => 'bar',
        ]);
        $saferPay->setModel($this->generatePayModel());
        $saferPay->setContext($this->generateContextController());

        $this->expectException(PaymentException::class);
        $r = $saferPay->create();
    }
}
