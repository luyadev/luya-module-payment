<?php

namespace luya\payment\tests;

use PHPUnit\Framework\TestCase;
use luya\Boot;

class BasePaymentTestCase extends TestCase
{
    protected function setUp()
    {
        $boot = new Boot();
        $boot->setConfigArray(['id' => 'paymenttest', 'basePath' => dirname(__DIR__)]);
        $boot->setYiiPath(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
        $boot->mockOnly = true;
        $boot->applicationWeb();
    }
}