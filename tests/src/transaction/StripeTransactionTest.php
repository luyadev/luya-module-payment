<?php

namespace luya\payment\tests\transaction;

use luya\payment\tests\BasePaymentTestCase;
use luya\payment\transaction\StripeTransaction;


class StripeTransactionTest extends BasePaymentTestCase
{
    public function testCreate()
    {
        $stripe = new StripeTransaction([
            'publishableKey' => 'foobar',
            'secretKey' => 'barfoo',
        ]);
        $stripe->setModel($this->generatePayModel());

        $r = $stripe->create();

        $this->assertNotNull($r);
    }
}