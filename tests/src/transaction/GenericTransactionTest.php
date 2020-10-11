<?php

namespace luya\payment\tests\transaction;

use luya\payment\base\Transaction;
use luya\payment\tests\BasePaymentTestCase;
use luya\payment\tests\data\DummyIntegrator;

class GenericTransactionTest extends BasePaymentTestCase
{
    public function testGenericMethods()
    {
        $transaction = new class extends Transaction
        {
            public function create()
            {
            }

            public function back()
            {
                $this->closePaymentAsSuccessful();   
            }

            public function notify()
            {
            }

            public function fail()
            {
                $this->closePaymentAsErrored();   
            }

            public function abort()
            {
                $this->closePaymentAsAborted();
            }
        };
        
        $integrator = new DummyIntegrator();
        $transaction->setIntegrator($integrator);
        $transaction->setModel($this->generatePayModel());
        $transaction->setContext($this->generateContextController());

        
        $this->assertEmpty($transaction->create());
        $this->assertEmpty($transaction->back());
        $this->assertEmpty($transaction->notify());
        $this->assertEmpty($transaction->fail());
        $this->assertEmpty($transaction->abort());
    }
}
