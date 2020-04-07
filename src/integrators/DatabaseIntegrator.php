<?php

namespace luya\payment\integrators;

use Yii;
use luya\payment\base\IntegratorInterface;
use luya\payment\base\PayModel;
use luya\payment\models\Process;
use luya\payment\models\ProcessTrace;

/**
 * Database integrator
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class DatabaseIntegrator implements IntegratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function createModel(PayModel $model)
    {
        $process = new Process();
        $process->amount = $model->totalAmount;
        $process->currency = $model->currency;
        $process->order_id = $model->orderId;
        $process->success_link = $model->successLink;
        $process->error_link = $model->errorLink;
        $process->abort_link = $model->abortLink;
        $process->close_state = Process::STATE_PENDING;
        $process->is_closed = $model->isClosed;
        $process->provider_data = $model->providerData;
        $items = [];
        foreach ($model->items as $item) {
            $items[] = [
                'qty' => $item->qty,
                'name' => $item->name,
                'amount' => $item->amount,
                'is_shipping' => $item->is_shipping,
                'is_tax' => $item->is_tax,
                'total_amount' => $item->getTotalAmount(),
            ];
        }
        $process->setItems($items);
        if ($process->insert()) {
            $model->setId($process->id);
            $model->setAuthToken($process->auth_token);
            $model->setRandomKey($process->random_key);
            return $model;
        }

        return false;
    }

    public function findByKey($key, $token)
    {
        $model = Process::find()->where(['random_key' => $key])->with(['items'])->one();

        if (!$model) {
            return false;
        }

        if ($this->validateProcess($model, $token)) {
            $model = $this->createPayModel($model);
            $model->setAuthToken($token);
            return $model;
        }
    }

    public function addTrace(PayModel $model, $event, $message = null)
    {
        $trace = new ProcessTrace();
        $trace->process_id = $model->getId();
        $trace->event = $event;
        $trace->message = $message;
        return $trace->save();
    }

    public function findById($id)
    {
        $process = Process::find()->where(['id' => $id])->with(['items'])->one();

        if (!$process) {
            return false;
        }

        return self::createPayModel($process);
    }

    public function closeModel(PayModel $model, $state)
    {
        $process = Process::find()->where(['id' => $model->getId(), 'is_closed' => 0])->one();

        if (!$process) {
            return false;
        }

        $process->is_closed = 1;
        $process->close_state = $state;
        $process->close_timestamp = time();
        
        return $process->update(true, ['is_closed', 'close_state', 'close_timestamp']);
    }

    public function saveProviderData(PayModel $model, array $data)
    {
        $process = Process::find()->where(['id' => $model->getId()])->one();

        $process->provider_data = $data;

        return $process->update(true, ['provider_data']);
    }

    /* internal methods */

    private function validateProcess(Process $process, $token)
    {
        $process->auth_token = $token;

        return $process->validateAuthToken();
    }

    private function createPayModel(Process $process)
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
        $model->authToken = $process->auth_token;
        $model->closeState = $process->close_state;
        $model->isClosed = $process->is_closed;
        $model->providerData = $process->provider_data;

        // assign items from origin process modell
        foreach ($process->items as $item) {
            $model->addItem($item->name, $item->qty, $item->amount, $item->total_amount, $item->is_shipping, $item->is_tax);
        }
        
        if ($model->validate()) {
            return $model;
        }

        return false;
    }
}
