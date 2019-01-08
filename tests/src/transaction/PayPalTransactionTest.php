<?php

namespace luya\payment\tests\transaction;

use luya\payment\tests\BasePaymentTestCase;
use luya\payment\transactions\PayPalTransaction;


class PayPalTransactiontest extends BasePaymentTestCase
{
    public function testCreate()
    {
        $paypal = new PayPalTransaction([
            'clientId' => 'clientid',
            'clientSecret' => 'secret',
            'mode' => PayPalTransaction::MODE_LIVE,
        ]);

        $paypal->setModel($this->generatePayModel());
        $paypal->setContext($this->generateContextController());

        $this->expectExceptionMessage('PayPal Exception: Got Http response code 401 when accessing https://api.paypal.com/v1/oauth2/token');
        $paypal->create();
    }
}