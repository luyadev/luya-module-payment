<?php

namespace luya\payment\tests\data;

use luya\payment\base\IntegratorInterface;
use luya\payment\base\PayModel;

class DummyIntegrator implements IntegratorInterface
{
    /**
     * Create new Payment process entry based on PayModel
     *
     * @param PayModel $model
     */
    public function createModel(PayModel $model)
    {
        return true;
    }

    /**
     * Find a payment process entry based on the key and token returns the PayModel
     *
     * @param string $key The random key
     * @param string $token The random token
     * @return PayModel
     */
    public function findByKey($key, $token)
    {
        return true;
    }

    /**
     * Find a payment process based on the ID and returns the PayModel.
     *
     * @param integer $id The ID of the process
     * @return PayModel
     */
    public function findById($id)
    {
        return true;
    }

    /**
     * Close a PayModel based on the state.
     *
     * @param PayModel $model
     * @param integer $state An Integer value to close the model
     * @return boolean
     */
    public function closeModel(PayModel $model, $state)
    {
        return true;
    }

    /**
     * Add A trace information for a current PayModel process.
     *
     * @param PayModel $model
     * @param string $event
     * @param string $message
     * @return boolean
     */
    public function addTrace(PayModel $model, $event, $message = null)
    {
        return true;
    }
}