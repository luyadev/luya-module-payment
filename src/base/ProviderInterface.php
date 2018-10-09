<?php

namespace luya\payment\base;

/**
 * Payment Provider Interface.
 *
 * The Payment Provider Interface describtes the basic informations for a payment Gateway.
 *
 * @author Basil Suter <basil@nadar.io>
 */
interface ProviderInterface
{
    /**
     * Return the name of the Provider.
     * 
     * An unique identifier for this provider, examples:
     * 
     * + strip
     * + paypal
     * + saferpay
     *
     * @return void
     */
    public function getId();
}
