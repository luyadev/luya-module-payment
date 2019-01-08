<?php

namespace luya\payment\providers;

use luya\payment\base\Provider;


class StripeProvider extends Provider
{
    public function getId()
    {
        return 'stripe';
    }
}