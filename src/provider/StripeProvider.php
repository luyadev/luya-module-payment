<?php

namespace luya\payment\provider;

use luya\payment\base\Provider;


class StripeProvider extends Provider
{
    public function getId()
    {
        return 'stripe';
    }

    public function callCreate($apiKey, $amount, $currency, $source)
    {
        
    }
}