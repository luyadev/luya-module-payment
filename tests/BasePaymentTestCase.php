<?php

namespace luya\payment\tests;

use PHPUnit\Framework\TestCase;
use luya\Boot;

class BasePaymentTestCase extends TestCase
{
    protected function setUp()
    {
        $boot = new Boot();
        $boot->setConfigArray([
            'id' => 'paymenttest',
            'basePath' => dirname(__DIR__),
            'modules' => [
                'payment' => [
                    'class' => 'luya\payment\Module',
                ]
            ],
            'components' => [
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => DB_DSN,
                    'username' => DB_USER,
                    'password' => DB_PASS,
                    'charset' => 'utf8',
                ]
            ]
        ]);
        $boot->setYiiPath(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
        $boot->mockOnly = true;
        $boot->applicationWeb();
    }
}
