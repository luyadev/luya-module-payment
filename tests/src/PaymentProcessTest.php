<?php

namespace luya\payment\tests;

use Yii;
use luya\payment\tests\data\DummyTransaction;
use luya\payment\models\Process;
use luya\payment\Pay;
use luya\payment\integrators\DatabaseIntegrator;

class PaymentProcessTest extends BasePaymentTestCase
{
    public function testInitException()
    {
        $this->expectException('luya\payment\PaymentException');
        $process = new Pay();
        $process->getId();
    }
    
    public function testPaymentProcessObject()
    {
        $object = new Pay();
        /*
        [
            'orderId' => 123,
            'currency' => 'EUR',
            'successLink' => '/success',
            'errorLink' => '/error',
            'abortLink' => '/abort'
        ]);
        */
        $object->setOrderId(123);
        $object->setCurrency('EUR');
        $object->setSuccessLink(['/success']);
        $object->setErrorLink(['/error']);
        $object->setAbortLink(['/abort']);
        
        $object->addItem('Product 1', 1, 100);

        /*
        $transaction = $object->getTransaction();

        $this->assertInstanceOf('\luya\payment\base\TransactionInterface', $transaction);
        */

        
        
        $processId = $object->getId();
        
        $this->assertNotFalse($processId);
        
        $model = DatabaseIntegrator::findByKey($object->getRandomKey(), $object->getAuthToken());
        $this->assertInstanceOf('\luya\payment\base\PayModel', $model);
        
        $token = $model->getAuthToken();
        
        $randomKey = $model->randomKey;
        
        // find payment process by processId

        $object2 = Pay::findById($processId);
        
        $this->assertInstanceOf('\luya\payment\base\PayModel', $object2);
        
        $this->assertSame($processId, $object2->getId());
        // this mus throw an exception
        //$this->assertSame(null, $object2->getAuthToken()); // as the token can not be reassigned, it must be null

        // find payment by token:

        $object3 = DatabaseIntegrator::findByKey($randomKey, $token);
        
        $this->assertInstanceOf('\luya\payment\base\PayModel', $object3);
        $this->assertSame($processId, $object3->getId());
        $this->assertSame($token, $object3->getAuthTOken());
        
        // close the model

        $c = Pay::close($processId, Pay::STATE_SUCCESS);
        $this->assertNotFalse($c);
    }
    
    public function testErrorAmountProcess()
    {
        $this->expectException('luya\payment\PaymentException');
        $object = new Pay();
        $object->getId();
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

        $object = new Pay();

        $object->setOrderId(123);
        $object->setCurrency('EUR');
        $object->setSuccessLink(['/success']);
        $object->setErrorLink(['/error']);
        $object->setAbortLink(['/abort']);
        $object->addItem('Product 1', 1, 100);

        $token = $object->getAuthToken();
        $key = $object->getRandomKey();

        $model = DatabaseIntegrator::findByKey($key, $token);

        $this->assertContains('payment-create', $model->getTransactionGatewayCreateLink());
        $this->assertContains('payment-abort', $model->getTransactionGatewayAbortLink());
        $this->assertContains('payment-back', $model->getTransactionGatewayBackLink());
        $this->assertContains('payment-fail', $model->getTransactionGatewayFailLink());
        $this->assertContains('payment-notify', $model->getTransactionGatewayNotifyLink());
    }
}
