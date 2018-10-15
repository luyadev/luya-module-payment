<?php

namespace luya\payment\tests;

use luya\payment\Pay;


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
}