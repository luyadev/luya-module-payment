<?php

namespace luya\payment\providers;

use luya\payment\base\Provider;

/**
 * Strip Provider
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class StripeProvider extends Provider
{
    public function getId()
    {
        return 'stripe';
    }
}
