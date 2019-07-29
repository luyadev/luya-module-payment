<?php

namespace luya\payment\tests\transaction;

use Yii;
use luya\payment\tests\BasePaymentTestCase;
use luya\payment\transactions\StripeTransaction;
use yii\web\Controller;
use yii\base\Response;
use luya\payment\integrators\DatabaseIntegrator;
use luya\payment\tests\data\DummyIntegrator;

class StripeTransactionTest extends BasePaymentTestCase
{
    public function testCreate()
    {
        $stripe = new StripeTransaction([
            'publishableKey' => 'foobar',
            'secretKey' => 'barfoo',
            'layout' => false,
        ]);
        $stripe->setModel($this->generatePayModel());

        $r = $stripe->create();

        $this->assertNotNull($r);
    }

    public function testWithPostCreate()
    {
        $stripe = new StripeTransaction([
            'publishableKey' => 'foobar',
            'secretKey' => 'barfoo',
            'layout' => false,
        ]);
        $stripe->setModel($this->generatePayModel());
        $controller = new Controller('id', $this->app);
        $stripe->setContext($controller);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        Yii::$app->request->setBodyParams(['payment_method_id' => 1]);

        $response = $stripe->create();
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testUnsupportedMethods()
    {
        $stripe = new StripeTransaction([
            'publishableKey' => 'foobar',
            'secretKey' => 'barfoo',
            'layout' => false,
        ]);
        $integrator = new DummyIntegrator();
        $stripe->setIntegrator($integrator);
        $stripe->setModel($this->generatePayModel());
        $controller = new Controller('id', $this->app);
        $stripe->setContext($controller);

        $this->assertInstanceOf(Response::class, $stripe->fail());
        $this->assertInstanceOf(Response::class, $stripe->abort());

        $this->expectException('luya\payment\PaymentException');
        $stripe->back();
        $stripe->notify();
    }
}
