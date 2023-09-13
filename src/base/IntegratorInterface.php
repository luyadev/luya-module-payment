<?php

namespace luya\payment\base;

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
     * Create new Payment process entry based on PayModel.
     *
     * The PayModel will be returned and recieved the update values from the integrator.
     *
     * Following attributes must be updated/added by this process:
     *
     * + $model->setId();
     * + $model->setAuthToken($api->auth_token);
     * + $model->setRandomKey($api->random_key);
     *
     * @param PayModel $model
     * @return PayModel|boolean If successfull the PayModel will be returned with updated values, otherwise false.
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
     * @param string $event The event is commonly the method which is running the trace f.e. `create`, `back`, `fail`, `abort`, `notify`.
     * @param string $message
     * @return boolean
     */
    public function addTrace(PayModel $model, $event, $message = null);

    /**
     * Save additonal arrayabe data from the payment provider.
     *
     * Used to store informations like payment process ID from the payment provider itself (like stripe transaction id).
     *
     * @param PayModel $model
     * @param array $data
     * @return boolean
     * @since 2.0.0
     */
    public function saveProviderData(PayModel $model, array $data);

    /**
     * Returns all stored informations.
     *
     * @param PayModel $model
     * @return array
     * @since 3.0.0
     */
    public function getProviderData(PayModel $model): array;
}
