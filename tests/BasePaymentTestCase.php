<?php

namespace luya\payment\tests;

use PHPUnit\Framework\TestCase;
use luya\Boot;
use luya\testsuite\cases\WebApplicationTestCase;
use luya\testsuite\fixtures\ActiveRecordFixture;
use luya\payment\models\DataPaymentProcessModel;
use luya\payment\models\DataPaymentProcessTraceModel;

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
                        'dsn' => 'sqlite::memory:',
                    ]
                ]
        ];
    }

    public $fixtureProcessModel;
    
    public $fixtureProcessTraceModel;

    public function afterSetup()
    {
        parent::afterSetup();

        $this->fixtureProcessModel = new ActiveRecordFixture([
            'modelClass' => DataPaymentProcessModel::class,
            'fixtureData' => [
            ]
        ]);

        $this->fixtureProcessTraceModel = new ActiveRecordFixture([
            'modelClass' => DataPaymentProcessTraceModel::class,
        ]);
    }

    public function beforeTearDown()
    {
        parent::beforeTearDown();

        $this->fixtureProcessModel->cleanup();
        $this->fixtureProcessTraceModel->cleanup();
    }
}
