<?php

namespace luya\payment\tests\transaction;

use luya\payment\tests\BasePaymentTestCase;
use luya\payment\transaction\PayPalTransaction;


class PayPalTransactiontest extends BasePaymentTestCase
{
    public function testCreate()
    {
        $paypal = new PayPalTransaction([
            'clientId' => 'clientid',
            'clientSecret' => 'secret',
        ]);

        $paypal->setModel(($this->generatePayModel()));
        $paypal->setContext($this->generateContextController());

        $this->expectExceptionMessage('PayPal Exception: Got Http response code 401 when accessing https://api.paypal.com/v1/oauth2/token');
        $paypal->create();
    }
}