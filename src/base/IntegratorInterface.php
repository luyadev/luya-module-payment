<?php

namespace luya\payment\base;

use luya\web\Controller;

/**
 * Integrator Interface.
 *
 * This interfaces enables the option to choose whether payment informations are sent
 * directly into a database or a headless API.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
interface IntegratorInterface
{
    /**
     * Create new Payment process entry based on PayModel
     *
     * @param PayModel $model
     */
    public function createModel(PayModel $model);

    /**
     * Find a payment process entry based on the key and token returns the PayModel
     *
     * @param string $key The random key
     * @param string $token The random token
     * @return PayModel
     */
    public function findByKey($key, $token);

    /**
     * Find a payment process based on the ID and returns the PayModel.
     *
     * @param integer $id The ID of the process
     * @return PayModel
     */
    public function findById($id);

    /**
     * Close a PayModel based on the state.
     *
     * @param PayModel $model
     * @param integer $state An Integer value to close the model
     * @return boolean
     */
    public function closeModel(PayModel $model, $state);

    /**
     * Add A trace information for a current PayModel process.
     *
     * @param PayModel $model
     * @param string $event
     * @param string $message
     * @return boolean
     */
    public function addTrace(PayModel $model, $event, $message = null);

    public function saveProviderData(PayModel $model, array $data);
}
