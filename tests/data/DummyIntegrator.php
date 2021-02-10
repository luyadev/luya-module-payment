<?php

namespace luya\payment\tests\data;

use luya\payment\base\IntegratorInterface;
use luya\payment\base\PayModel;

class DummyIntegrator implements IntegratorInterface
{
    public $createModelResponse = true;

    public $closeModelResponse = true;
    
    /**
     * {@inheritDoc}
     */
    public function createModel(PayModel $model)
    {
        if ($this->createModelResponse) {
            $model->setId(1);
            $model->setRandomKey('123');
            $model->setAuthToken('123');
            return $model;
        }
        return $this->createModelResponse;
    }

    /**
     * {@inheritDoc}
     */
    public function findByKey($key, $token)
    {
        return new PayModel();
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
        return $this->closeModelResponse;
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

    public function getProviderData(PayModel $model): array
    {
        return [];
    }
}
