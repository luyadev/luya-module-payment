<?php

namespace luya\payment\base;

/**
 * Payment Provider Interface.
 *
 * The Payment Provider Interface describtes the basic informations for a payment Gateway.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
interface ProviderInterface
{
    /**
     * Return the name of the Provider.
     *
     * An unique identifier for this provider, examples:
     *
     * + strip
     * + saferpay
     *
     * @return void
     */
    public function getId();
}
