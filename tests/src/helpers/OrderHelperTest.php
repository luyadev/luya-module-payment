<?php

namespace luya\payment\tests\helpers;

use luya\helpers\StringHelper;
use luya\payment\helpers\OrderHelper;
use luya\payment\tests\BasePaymentTestCase;

class OrderHelperTest extends BasePaymentTestCase
{
    public function testGenerateOrderId()
    {
        $this->assertStringContainsString('00004', OrderHelper::generateOrderId(4));
        $this->assertStringContainsString('00010', OrderHelper::generateOrderId(10));

        $this->assertStringContainsString('00004', OrderHelper::generateOrderId(4, 5));

        for ($i = 1;$i <= 100;$i++) {
            $string = OrderHelper::generateOrderId(1, 5, 10);

            $this->assertFalse(StringHelper::contains(['-', '_'], $string));
        }
    }

    public function testGenerateOrderIdZeros()
    {
        $this->assertStringContainsString('0000004', OrderHelper::generateOrderId(4, 7));
        $this->assertStringContainsString('0000010', OrderHelper::generateOrderId(10, 7));
    }

    public function testPrefix()
    {
        $this->assertSame('FOO_0004', OrderHelper::generateOrderId(4, 4, 0, 'FOO_'));
    }
}
