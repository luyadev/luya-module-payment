<?php

namespace luya\payment\integrators\headless;

use luya\headless\ActiveEndpoint;
use luya\headless\Client;

/**
 * Payment Process API
 */
class ApiPaymentProcess extends ActiveEndpoint
{
    public $items = [];
    public $id;
    public $amount;
    public $currency;
    public $order_id;
    public $success_link;
    public $error_link;
    public $abort_link;
    public $close_state;
    public $is_closed;
    public $auth_token;
    public $random_key;

    public function getEndpointName()
    {
        return '{{%api-payment-process}}';
    }

    public static function find()
    {
        return parent::find()->setExpand(['items']);
    }

    public static function findByKey($key, $token, Client $client)
    {
        return self::find()->setEndpoint('{endpointName}/find-by-key')->setArgs(['key' => $key, 'token' => $token])->one($client);
    }

    public function loadItems(array $items)
    {
        foreach ($items as $item) {
            $this->items[] = [
                'qty' => $item->qty,
                'name' => $item->name,
                'amount' => $item->amount,
                'is_shipping' => $item->is_shipping,
                'is_tax' => $item->is_tax,
                'total_amount' => $item->getTotalAmount(),
            ];
        }
    }
}