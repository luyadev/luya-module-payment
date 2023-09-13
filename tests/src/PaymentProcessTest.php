<?php

namespace luya\payment\tests;

use luya\payment\models\Process;
use luya\payment\Pay;

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
        $object->setTotalAmount(200);

        $object->addItem('Product 1', 2, 100);

        /*
        $transaction = $object->getTransaction();

        $this->assertInstanceOf('\luya\payment\base\TransactionInterface', $transaction);
        */



        $processId = $object->getId();

        $this->assertNotFalse($processId);

        $int = $this->app->getModule('payment')->getIntegrator();
        $model = $int->findByKey($object->getRandomKey(), $object->getAuthToken());
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

        $object3 = $int->findByKey($randomKey, $token);

        $this->assertInstanceOf('\luya\payment\base\PayModel', $object3);
        $this->assertSame($processId, $object3->getId());
        $this->assertSame($token, $object3->getAuthTOken());

        // there is not redirect trough the payment process and therefore the model is not set to success!
        $this->assertFalse(Pay::isSuccess($processId));
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
        $object->setTotalAmount(100);

        $token = $object->getAuthToken();
        $key = $object->getRandomKey();

        $int = $this->app->getModule('payment')->getIntegrator();
        $model = $int->findByKey($key, $token);

        $this->assertStringContainsString('payment-create', $model->getTransactionGatewayCreateLink());
        $this->assertStringContainsString('payment-abort', $model->getTransactionGatewayAbortLink());
        $this->assertStringContainsString('payment-back', $model->getTransactionGatewayBackLink());
        $this->assertStringContainsString('payment-fail', $model->getTransactionGatewayFailLink());
        $this->assertStringContainsString('payment-notify', $model->getTransactionGatewayNotifyLink());
    }
}
