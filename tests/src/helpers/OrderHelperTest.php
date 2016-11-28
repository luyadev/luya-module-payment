<?php

namespace luya\payment\tests\helpers;

use luya\payment\tests\BasePaymentTestCase;
use luya\payment\helpers\OrderHelper;

class OrderHelperTest extends BasePaymentTestCase
{
    public function testGenerateOrderId()
    {
        $this->assertContains('00004', OrderHelper::generateOrderId(4));
        $this->assertContains('00010', OrderHelper::generateOrderId(10));
    }
    
    public function testGenerateOrderIdZeros()
    {
        $this->assertContains('0000004', OrderHelper::generateOrderId(4, 7));
        $this->assertContains('0000010', OrderHelper::generateOrderId(10, 7));
    }
}
