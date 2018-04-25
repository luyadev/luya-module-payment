<?php

namespace luya\payment\tests;

use PHPUnit\Framework\TestCase;
use luya\Boot;
use luya\testsuite\cases\WebApplicationTestCase;

class BasePaymentTestCase extends WebApplicationTestCase
{
    public function getConfigArray()
    {
            return [
                'id' => 'paymenttest',
                'basePath' => dirname(__DIR__),
                'modules' => [
                    'payment' => [
                        'class' => 'luya\payment\Module',
                        'transaction' => ['class' => 'luya\payment\tests\data\DummyTransaction']
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
        ];
    }
}
