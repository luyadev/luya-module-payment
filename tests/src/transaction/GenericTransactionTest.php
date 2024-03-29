<?php

namespace luya\payment\tests\transaction;

use luya\payment\base\Transaction;
use luya\payment\PaymentException;
use luya\payment\tests\BasePaymentTestCase;
use luya\payment\tests\data\DummyIntegrator;
use yii\web\Response;

class GenericTransactionTest extends BasePaymentTestCase
{
    /**
     * @return Transaction
     */
    private function getTransaction()
    {
        return new class () extends Transaction {
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

            public function successCurl()
            {
                return $this->curlApplicationLink('https://luya.io');
            }

            public function errorCurl()
            {
                return $this->curlApplicationLink('https://thsdomainanditssubelveldoesnotexsts.comm');
            }

            public function errorStatusCurl()
            {
                return $this->curlApplicationLink('https://luya.io/admin/does/not/exists/at/all');
            }
        };
    }
    public function testGenericMethods()
    {
        $transaction = $this->getTransaction();
        $integrator = new DummyIntegrator();
        $transaction->setIntegrator($integrator);
        $transaction->setModel($this->generatePayModel());
        $transaction->setContext($this->generateContextController());


        $this->assertEmpty($transaction->create());
        $this->assertEmpty($transaction->back());
        $this->assertEmpty($transaction->notify());
        $this->assertEmpty($transaction->fail());
        $this->assertEmpty($transaction->abort());

        $this->assertTrue($transaction->successCurl());
        $this->assertFalse($transaction->errorCurl());
        $this->assertFalse($transaction->errorStatusCurl());

        $this->assertInstanceOf(Response::class, $transaction->redirectApplicationSuccess());
        $this->assertInstanceOf(Response::class, $transaction->redirectApplicationAbort());
        $this->assertInstanceOf(Response::class, $transaction->redirectApplicationError());
        $this->assertInstanceOf(Response::class, $transaction->redirectTransactionAbort());
        $this->assertInstanceOf(Response::class, $transaction->redirectTransactionFail());
        $this->assertInstanceOf(Response::class, $transaction->redirectTransactionNotify());
        $this->assertInstanceOf(Response::class, $transaction->redirectTransactionBack());
    }

    public function testBackException()
    {
        $transaction = $this->getTransaction();
        $integrator = new DummyIntegrator();
        $integrator->closeModelResponse = false;
        $transaction->setIntegrator($integrator);
        $transaction->setModel($this->generatePayModel());
        $transaction->setContext($this->generateContextController());

        $this->expectException(PaymentException::class);
        $transaction->back();
    }

    public function testFailException()
    {
        $transaction = $this->getTransaction();
        $integrator = new DummyIntegrator();
        $integrator->closeModelResponse = false;
        $transaction->setIntegrator($integrator);
        $transaction->setModel($this->generatePayModel());
        $transaction->setContext($this->generateContextController());

        $this->expectException(PaymentException::class);
        $transaction->fail();
    }

    public function testAbortException()
    {
        $transaction = $this->getTransaction();
        $integrator = new DummyIntegrator();
        $integrator->closeModelResponse = false;
        $transaction->setIntegrator($integrator);
        $transaction->setModel($this->generatePayModel());
        $transaction->setContext($this->generateContextController());

        $this->expectException(PaymentException::class);
        $transaction->abort();
    }
}
