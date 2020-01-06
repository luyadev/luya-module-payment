<?php

namespace luya\payment\tests\helpers;

use luya\helpers\StringHelper;
use luya\payment\tests\BasePaymentTestCase;
use luya\payment\helpers\OrderHelper;

class OrderHelperTest extends BasePaymentTestCase
{
    public function testGenerateOrderId()
    {
        $this->assertContains('00004', OrderHelper::generateOrderId(4));
        $this->assertContains('00010', OrderHelper::generateOrderId(10));

        $this->assertContains('00004', OrderHelper::generateOrderId(4,5));

        for ($i=1;$i<=100;$i++) {
            $string = OrderHelper::generateOrderId(1, 5, 10);

            $this->assertFalse(StringHelper::contains(['-', '_'], $string));
        }
    }
    
    public function testGenerateOrderIdZeros()
    {
        $this->assertContains('0000004', OrderHelper::generateOrderId(4, 7));
        $this->assertContains('0000010', OrderHelper::generateOrderId(10, 7));
    }
}
