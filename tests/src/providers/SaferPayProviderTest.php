<?php

namespace luya\payment\tests\providers;

use luya\payment\PaymentException;
use luya\payment\providers\SaferPayProvider;
use luya\payment\tests\BasePaymentTestCase;
use luya\payment\transactions\SaferPayTransaction;

class SaferPayProviderTest extends BasePaymentTestCase
{
    private function generateTransaction()
    {
        $saferPay = new SaferPayTransaction([
            'terminalId' => 1,
            'customerId' => 1,
            'username' => 'foo',
            'password' => 'bar',
        ]);
        $saferPay->setModel($this->generatePayModel());
        $saferPay->setContext($this->generateContextController());
        $saferPay->setIntegrator($this->generateIntegrator());

        return $saferPay;
    }

    public function testAssertCall()
    {
        $provider = new SaferPayProvider();
        $provider->transaction = $this->generateTransaction();
        $this->expectException(PaymentException::class);
        $provider->assert('requestid', 'token');
    }

    public function testCaptureCall()
    {
        $provider = new SaferPayProvider();
        $provider->transaction = $this->generateTransaction();
        $x = $provider->capture('requestid', 'transactionid');

        $this->assertSame('AUTHENTICATION_FAILED', $x['ErrorName']);
    }
}
