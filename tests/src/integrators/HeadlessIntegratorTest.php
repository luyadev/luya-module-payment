<?php

namespace luya\payment\tests\integrators;

use luya\headless\exceptions\RequestException;
use luya\payment\base\PayModel;
use luya\payment\integrators\HeadlessIntegrator;
use luya\payment\PaymentException;
use luya\payment\tests\BasePaymentTestCase;

class HeadlessIntegratorTest extends BasePaymentTestCase
{
    /**
     * @return PayModel
     */
    private function getPayModel()
    {
        return new PayModel([
            'id' => 1,
            'totalAmount' => 200,
            'orderId' => 1,
            'currency' => 'CHF',
            'successLink' => 'link',
            'abortLink' => 'link',
            'errorLink' => 'link',
            'authToken' => 'link',
            'randomKey' => 'link',
            'items' => [],
            'isClosed' => 0,
            'closeState' => 'link',
            'providerData' => [],
        ]);
    }

    public function testInvalidApiOnCreate()
    {
        $integrator = new HeadlessIntegrator();

        $this->expectException(RequestException::class);
        $integrator->createModel($this->getPayModel());
    }

    public function testInvalidApiOnFindById()
    {
        $integrator = new HeadlessIntegrator();

        $this->assertFalse($integrator->findById(1));
        $this->assertFalse($integrator->findByKey('key', 'token'));
        $this->assertFalse($integrator->closeModel($this->getPayModel(), 1));

        $this->assertFalse($integrator->saveProviderData($this->getPayModel(), ['foo' => 'bar']));

        $this->expectException(PaymentException::class);
        $integrator->getProviderData($this->getPayModel());
    }
}
