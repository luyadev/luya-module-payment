<?php

namespace luya\payment\tests\controllers;

use luya\payment\frontend\controllers\DefaultController as ControllersDefaultController;
use luya\payment\tests\BasePaymentTestCase;

class DefaultControllerTest extends BasePaymentTestCase
{
    public function afterSetup()
    {
        parent::afterSetup();
        $this->app->getModule('payment')->integrator = ['class' => 'luya\payment\tests\data\DummyIntegrator'];
    }
    public function testControllerActions()
    {
        $ctrl = new ControllersDefaultController('id', $this->app->getModule('payment'));
        $response = $ctrl->actionNotify(0, 0);
        $this->assertNull($response);
        $response = $ctrl->actionAbort(0, 0);
        $this->assertNull($response);
        $response = $ctrl->actionBack(0, 0);
        $this->assertNull($response);
        $response = $ctrl->actionCreate(0, 0);
        $this->assertNull($response);
        $response = $ctrl->actionFail(0, 0);
        $this->assertNull($response);
    }
}
