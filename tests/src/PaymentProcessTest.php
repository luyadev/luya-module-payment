<?php

namespace luya\payment\tests;

use luya\payment\PaymentProcess;
use luya\payment\tests\data\DummyTransaction;

class PaymentProcessTest extends BasePaymentTestCase
{
    public function testPaymentProcessObject()
    {
        $object = new PaymentProcess([
            'transactionConfig' => [
                'class' => DummyTransaction::class,
            ],
            'amount' => 100,
            'orderId' => 123,
            'currency' => 'EUR',
            'successLink' => '/success',
            'errorLink' => '/error',
            'abortLink' => '/abort'
        ]);
        
        $transaction = $object->getTransaction();
        
        $this->assertInstanceOf('\luya\payment\base\TransactionInterface', $transaction);
        
        $processId = $object->getId();
        
        $this->assertNotFalse($processId);
        
        $this->assertInstanceOf('\luya\payment\models\DataPaymentProcessModel', $object->model);
        
        $token = $object->model->auth_token;
        $randomKey = $object->model->random_key;
        
        // find payment process by processId
        
        $object2 = PaymentProcess::findByProcessId($processId);
        
        $this->assertInstanceOf('\luya\payment\PaymentProcess', $object2);
        
        $this->assertSame($processId, $object2->getId());
        $this->assertSame(null, $object2->model->auth_token); // as the token can not be reassigned, it must be null
        
        // find payment by token:
        
        $object3 = PaymentProcess::findByToken($token, $randomKey);
        
        $this->assertInstanceOf('\luya\payment\PaymentProcess', $object3);
        $this->assertSame($processId, $object3->getId());
        $this->assertSame($token, $object3->model->auth_token);
        
        // close the model
        
        $object->close(PaymentProcess::STATE_SUCCESS);
        
        $this->assertSame(1, $object->model->close_state);
        $this->assertSame(1, $object->model->is_closed);
    }
    
    public function testErrorAmountProcess()
    {
        $this->expectException('luya\payment\PaymentException');
        $object = new PaymentProcess([
            'transactionConfig' => [
                'class' => DummyTransaction::class,
            ],
            'amount' => 'a123123',
            'currency' => 'EUR',
            'successLink' => '/success',
            'errorLink' => '/error',
            'abortLink' => '/abort'
        ]);
    }
}