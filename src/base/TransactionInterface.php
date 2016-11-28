<?php

namespace luya\payment\base;

/**
 * Transaction interface cycle description
 *
 * ** if the the method success is called it does not mean the transaction was successfull **
 *
 * successfull:
 *
 * + create
 * + return (multiple actions inside return)
 *
 * validation error:
 *
 * + create
 * + return
 * + fail
 *
 * use pushes stop on provider
 *
 * + create
 * + abort
 *
 * @author Basil Suter <basil@nadar.io>
 */
interface TransactionInterface
{
    /**
     * Creates the transaction and mostly redirects to the provider afterwards
     */
    public function create();
    
    /**
     * Return from create into the back
     */
    public function back();
    
    /**
     * Some providers provide a notify link
     */
    public function notify();
    
    /**
     * An error/failure happend
     */
    public function fail();
    
    /**
     * All providers provide an abort/stop link to back into the onlinestore and choose
     */
    public function abort();
    
    /**
     * Return the payment provider object.
     * 
     * Configuration Example:
     * 
     * ```php
     * return new PayPalProvider(['mode' => $this->mode]);
     * ```
     * 
     * @return \luya\payment\base\ProviderInterface The provider implementation
     */
    public function getProvider();
}
