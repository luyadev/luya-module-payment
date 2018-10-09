<?php

namespace luya\payment\tests;

use Yii;
use luya\payment\PaymentProcess;
use luya\payment\tests\data\DummyTransaction;
use luya\payment\models\DataPaymentProcessModel;

class PaymentProcessTest extends BasePaymentTestCase
{
    public function testInitException()
    {
        $this->expectException('luya\payment\PaymentException');
        $process = new PaymentProcess();
    }
    
    public function testPaymentProcessObject()
    {
        $object = new PaymentProcess([
            'amount' => 100,
            'orderId' => 123,
            'currency' => 'EUR',
            'successLink' => '/success',
            'errorLink' => '/error',
            'abortLink' => '/abort'
        ]);
        
        /*
        $transaction = $object->getTransaction();

        $this->assertInstanceOf('\luya\payment\base\TransactionInterface', $transaction);
        */

        
        
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
            'amount' => 'a123123',
            'currency' => 'EUR',
            'successLink' => '/success',
            'errorLink' => '/error',
            'abortLink' => '/abort'
        ]);
    }
    
    public function testUrlRules()
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['DOCUMENT_ROOT'] = '/var/www';
        $_SERVER['REQUEST_URI'] = '/luya/envs/dev/public_html/';
        $_SERVER['SCRIPT_NAME'] = '/luya/envs/dev/public_html/index.php';
        $_SERVER['PHP_SELF'] = '/luya/envs/dev/public_html/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/var/www/luya/envs/dev/public_html/index.php';

        $object = new PaymentProcess([
            'amount' => 100,
            'orderId' => 123,
            'currency' => 'EUR',
            'successLink' => '/success',
            'errorLink' => '/error',
            'abortLink' => '/abort'
        ]);
        
        $this->assertContains('payment-create', $object->getTransactionGatewayCreateLink());
        $this->assertContains('payment-abort', $object->getTransactionGatewayAbortLink());
        $this->assertContains('payment-back', $object->getTransactionGatewayBackLink());
        $this->assertContains('payment-fail', $object->getTransactionGatewayFailLink());
        $this->assertContains('payment-notify', $object->getTransactionGatewayNotifyLink());
    }
}
