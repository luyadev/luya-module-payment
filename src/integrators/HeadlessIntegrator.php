<?php

namespace luya\payment\integrators;

use luya\payment\base\IntegratorInterface;
use luya\payment\base\PayModel;
use luya\headless\Client;
use luya\payment\PaymentException;
use luya\payment\integrators\headless\ApiPaymentProcess;
use luya\payment\Pay;
use yii\base\BaseObject;

/**
 * Headless Payment:
 * 
 * You can either pass a callable with returns an headless client object or provide the accesstoken and server url.
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class HeadlessIntegrator extends BaseObject implements IntegratorInterface
{
    public $accessToken;
    public $serverUrl;

    private $_client;

    public function setClient($client)
    {
        if (is_callable($client)) {
            $client = call_user_func($client);
        }

        if (!$client instanceof Client) {
            throw new PaymentException("The given client must be an instance of luya\headless\Client");
        }

        $this->_client = $client;
    }

    public function getClient()
    {
        if ($this->_client === null) {
            $this->_client = new Client($this->accessToken, $this->serverUrl);
        }

        return $this->_client;
    }

    public function createModel(PayModel $model)
    {
        $api = new ApiPaymentProcess();
        $api->amount = $model->totalAmount;
        $api->currency = $model->currency;
        $api->order_id = $model->orderId;
        $api->success_link = $model->successLink;
        $api->error_link = $model->errorLink;
        $api->abort_link = $model->abortLink;
        $api->close_state = Pay::STATE_PENDING;
        $api->is_closed = $model->isClosed;
        $api->loadItems($model->items);
        if ($api->save($this->getClient())) {
            $model->setId($api->id);
            $model->setAuthToken($api->auth_token);
            $model->setRandomKey($api->random_key);
            return $model;
        }

        return false;
    }

    public function findByKey($key, $token)
    {
        $api = ApiPaymentProcess::findByKey($key, $token, $this->getClient());

        if (!$api) {
            return false;
        }

        $model = self::createPayModel($api);
        $model->setAuthToken($token);
        return $model;
    }

    public function findById($id)
    {
        $api = ApiPaymentProcess::viewOne($id, $this->getClient());

        if (!$api) {
            return false;
        }

        return self::createPayModel($api);
    }

    public function closeModel(PayModel $model, $state)
    {
        $api = ApiPaymentProcess::viewOne($model->getId(), $this->getClient());

        if (!$api || $api->is_closed) {
            return false;
        }

        $api->is_closed = 1;
        $api->close_state = $state;
        if ($api->save($this->getClient(), ['is_closed', 'close_state'])) {
            return true;
        }

        return false;
    }

    public function addTrace(PayModel $model, $event, $message = null)
    {

    }

    // internal

    private static function createPayModel(ApiPaymentProcess $process)
    {
        $model = new PayModel();
        $model->orderId = $process->order_id;
        $model->totalAmount = $process->amount;
        $model->currency = $process->currency;
        $model->randomKey = $process->random_key;
        $model->id = $process->id;
        $model->errorLink = $process->error_link;
        $model->successLink = $process->success_link;
        $model->abortLink = $process->abort_link;
        $model->items = $process->items;
        if ($model->validate()) {
            return $model;
        }

        return false;
    }
}