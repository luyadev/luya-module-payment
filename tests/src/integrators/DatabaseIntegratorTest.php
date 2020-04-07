<?php

namespace luya\payment\tests\integrators;

use luya\payment\base\PayModel;
use luya\payment\integrators\DatabaseIntegrator;
use luya\payment\tests\BasePaymentTestCase;

class DatabaseIntegratorTest extends BasePaymentTestCase
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

    public function testBasicFunction()
    {
        $integrator = new DatabaseIntegrator();

        $this->assertNotFalse($integrator->createModel($this->getPayModel()));
        $this->assertNotFalse($integrator->findById(1));
        $this->assertFalse($integrator->findByKey('key', 'token'));

        $integrator->addTrace($this->getPayModel(), 'foo', 'bar');
        $this->assertSame(1, $integrator->closeModel($this->getPayModel(), 1));

        $this->assertSame(1, $integrator->saveProviderData($this->getPayModel(), ['foo' => 'bar']));
    }
}