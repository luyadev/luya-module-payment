<?php

namespace luya\payment\base;

/**
 * Transaction Interface.
 *
 * Each transaction must implement the transaction interface.
 *
 * 1. create()
 * 2a. back()
 *    3a.notify()
 * 2b. fail()
 * 2c. abort()
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

    /**
     * Getter method for context.
     *
     * @return \yii\web\Controller
     */
    public function getContext();

    /**
     * Get the current payment process
     *
     * @return \luya\payment\PaymentProcess
     */
    public function getModel();

    public function setModel(PayModel $model);
}
