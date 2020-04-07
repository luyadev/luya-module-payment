<?php

namespace luya\payment\tests;

use luya\payment\Pay;
use luya\payment\PaymentException;
use luya\payment\tests\data\DummyIntegrator;
use yii\base\InvalidConfigException;

class PayTest extends BasePaymentTestCase
{
    public function testTotalAmountCalculations()
    {
        $pay = new Pay();
        $pay->setOrderId(123);
        $pay->setCurrency('EUR');
        $pay->setSuccessLink('success');
        $pay->setErrorLink('error');
        $pay->setAbortLink('abort');

        
        $pay->addItem('Product A', 2, 200); // total: 400
        $pay->addItem('Rabat B', 1, -100); // total: 300
        $pay->addItem('Free X', 1, 0); // 300
        $pay->addTax('VAT', 20); // total 320;
        $pay->addShipping('Shipping', 80); // total 400
        $pay->setTotalAmount(400);

        $this->assertNotFalse($pay->getId());
    }

    public function testInvalidConfig()
    {
        $pay = new Pay();
        $this->expectException(PaymentException::class);
        $pay->getId();
    }

    public function testInvalidAmount()
    {
        $pay = new Pay();
        $pay->setOrderId(123);
        $pay->setCurrency('EUR');
        $pay->setSuccessLink('success');
        $pay->setErrorLink('error');
        $pay->setAbortLink('abort');

        
        $pay->addItem('Product A', 2, 200); // total: 400
        $pay->addItem('Rabat B', 1, -100); // total: 300
        $pay->addItem('Free X', 1, 0); // 300
        $pay->addTax('VAT', 20); // total 320;
        $pay->addShipping('Shipping', 20); // total 400
        $pay->setTotalAmount(400);

        // 'The amount provided trough items,shipping & tax (340) must be equal the provided totalAmount (400).'
        $this->expectException(PaymentException::class);
        $pay->getId();
    }

    public function testInvalidModelValidation()
    {
        $pay = new Pay();
        $pay->setOrderId(""); // invalid rules
        $pay->setCurrency('EUR');
        $pay->setSuccessLink('success');
        $pay->setErrorLink('error');
        $pay->setAbortLink('abort');

        
        $pay->addItem('Product A', 2, 200); // total: 400
        $pay->addItem('Rabat B', 1, -100); // total: 300
        $pay->addItem('Free X', 1, 0); // 300
        $pay->addTax('VAT', 20); // total 320;
        $pay->addShipping('Shipping', 80); // total 400
        $pay->setTotalAmount(400);
        
        $this->expectException(PaymentException::class);
        $pay->getId();
    }

    public function testInvalidCurrencyException()
    {
        $pay = new Pay();
        $this->expectException(InvalidConfigException::class);
        $pay->setCurrency('EURO');
    }

    public function testCreateIntegratorModelError()
    {
        $this->app->getModule('payment')->integrator = ['class' => DummyIntegrator::class, 'createModelResponse' => false];
        $pay = new Pay();
        $pay->setOrderId(123);
        $pay->setCurrency('EUR');
        $pay->setSuccessLink('success');
        $pay->setErrorLink('error');
        $pay->setAbortLink('abort');

        
        $pay->addItem('Product A', 2, 200); // total: 400
        $pay->addItem('Rabat B', 1, -100); // total: 300
        $pay->addItem('Free X', 1, 0); // 300
        $pay->addTax('VAT', 20); // total 320;
        $pay->addShipping('Shipping', 80); // total 400
        $pay->setTotalAmount(400);

        // Error while creating the pay model by the integrator luya\payment\tests\data\DummyIntegrator
        $this->expectException(PaymentException::class);
        $pay->getId();
    }
}
