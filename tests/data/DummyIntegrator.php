<?php

namespace luya\payment\tests\data;

use luya\payment\base\IntegratorInterface;
use luya\payment\base\PayModel;

class DummyIntegrator implements IntegratorInterface
{
    public $createModelResponse = true;
    
    /**
     * {@inheritDoc}
     */
    public function createModel(PayModel $model)
    {
        return $this->createModelResponse;
    }

    /**
     * {@inheritDoc}
     */
    public function findByKey($key, $token)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function findById($id)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function closeModel(PayModel $model, $state)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function addTrace(PayModel $model, $event, $message = null)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function saveProviderData(PayModel $model, array $data)
    {
        return true;
    }
}