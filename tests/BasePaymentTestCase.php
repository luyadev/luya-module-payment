<?php

namespace luya\payment\tests;

use PHPUnit\Framework\TestCase;
use luya\Boot;
use luya\testsuite\cases\WebApplicationTestCase;
use luya\testsuite\fixtures\ActiveRecordFixture;
use luya\payment\models\Process;
use luya\payment\models\ProcessTrace;
use luya\payment\models\ProcessItem;
use luya\payment\base\PayModel;
use luya\payment\frontend\controllers\DefaultController;

class BasePaymentTestCase extends WebApplicationTestCase
{
    public function getConfigArray()
    {
        return [
                'id' => 'paymenttest',
                'basePath' => dirname(__DIR__),
                'modules' => [
                    'payment' => [
                        'class' => 'luya\payment\frontend\Module',
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

    public $fixtureProcessItemModel;
    
    public $fixtureProcessTraceModel;

    public function afterSetup()
    {
        parent::afterSetup();

        $this->fixtureProcessModel = new ActiveRecordFixture([
            'modelClass' => Process::class,
            'fixtureData' => [
            ]
        ]);

        $this->fixtureProcessTraceModel = new ActiveRecordFixture([
            'modelClass' => ProcessTrace::class,
        ]);

        $this->fixtureProcessItemModel = new ActiveRecordFixture([
            'modelClass' => ProcessItem::class,
        ]);
    }

    protected function generatePayModel()
    {
        $model = new PayModel();
        $model->setAuthToken('authtoken');
        $model->setRandomKey('randomkey');
        $model->id = 1;
        $model->totalAmount = 100;
        $model->orderId = 1;
        $model->currency = 'EUR';
        $model->successLink = '#link';
        $model->abortLink = '#abrot';
        $model->errorLink = '#error';

        return $model;
    }

    protected function generateContextController()
    {
        $ctrl = new DefaultController('default', $this->app);

        return $ctrl;
    }

    public function beforeTearDown()
    {
        parent::beforeTearDown();

        $this->fixtureProcessModel->cleanup();
        $this->fixtureProcessTraceModel->cleanup();
    }
}
