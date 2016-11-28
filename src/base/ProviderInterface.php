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
    public function getId();
    
    public function call($method, array $vars = []);
}
