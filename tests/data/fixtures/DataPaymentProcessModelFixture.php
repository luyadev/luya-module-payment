<?php

namespace luya\payment\tests\data\fixtures;

use yii\test\ActiveFixture;

class DataPaymentProcessModelFixture extends ActiveFixture
{
    public $modelClass = 'luya\payment\models\DataPaymentProcessModel';
    
    public function getData()
    {
        return [
            'process1' => [
                'id' => '1',
                'salt' => '1234',
                'hash' => '1234',
                'random_key' => '12345678',
                'amount' => '100',
                'currency' => 'EUR',
                'order_id' => 'OrderId123',
                'provider_name' => 'saferpay',
                'success_link' => 'localhost/success',
                'error_link' => 'localhost/error',
                'abort_link' => 'localhost/abort',
                'transaction_config' => '[{"class": "luya\payment\tests\DummyTransaction"}]',
                'close_state' => '0',
                'is_closed' => '0',
            ],
        ];
    }
}
