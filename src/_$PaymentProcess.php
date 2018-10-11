<?php

namespace luya\payment;

use Yii;
use luya\web\Controller;
use luya\helpers\Url;
use luya\Exception;
use luya\payment\base\TransactionInterface;
use luya\payment\base\PaymentProcessInterface;
use luya\payment\PaymentException;
use luya\payment\models\Process;
use yii\base\BaseObject;
use luya\payment\models\ProcessItem;

/**
 * Main PaymentProcess class.
 *
 * This method is used to make the calls, redirects and configuration of your Payment Interface.
 *
 * Example configuration for the initializing:
 *
 * ```php
 * $process = new payment\PaymentProcess([
 *     'orderId' => $orderId,
 *     'currency' => 'USD',
 *     'successLink' => ['/mystore/store-checkout/success', 'orderId' => $orderId], // user has paid successfull
 *     'errorLink' => ['/mystore/store-checkout/error', 'orderId' => $orderId], // user got a payment error
 *     'abortLink' => ['/mystore/store-checkout/abort', 'orderId' => $orderId], // user has pushed the back button
 * ]);
 * 
 * $process->addItem('My Product', 1', 10000); // which is 100 euros. Amount in cents (smallest currency value), 100 cents = 1 eur
 *
 * $processId = $process->getId();
 * ```
 *
 * The above example will generate a new payment process you can dispatch ($process->dispatch($this)).
 *
 * The processId is very important in order to retrieve your process model in later point of your application `PaymentProcess::findByProcessId($processId)`.
 *
 *
 *
 * @property \luya\payment\models\Process $model Get the payment process data model.
 * @property integer $id Returns the Process ID to store in your E-Store logic.
 *
 * @author Basil Suter <basil@nadar.io>
 */
final class PaymentProcess implements PaymentProcessInterface
{
    const STATE_PENDING = 0;

    const STATE_SUCCESS = 1;
    
    const STATE_ERROR = 2;
    
    const STATE_ABORT = 3;
    
    protected function checkConfig()
    {
        if (empty($this->_orderId) || empty($this->_currency) || is_null($this->_successLink) || is_null($this->_errorLink) || is_null($this->_abortLink)) {
            throw new PaymentException("orderId, currency, successLink, errorLink and abortLink properties can not be null!");
        }
    }
    

    private $_successLink;

    public function setSuccessLink($link)
    {
        $this->_successLink = is_array($link) ? Url::toRoute($link, true) : $link;
    }

    private $_errorLink;

    public function setErrorLink($link)
    {
        $this->_errorLink = is_array($link) ? Url::toRoute($link, true) : $link;
    }

    private $_abortLink;

    public function setAbortLink($link)
    {
        $this->_abortLink = is_array($link) ? Url::toRoute($link, true) : $link;
    }

    public function getTotalAmount()
    {
        $amount = 0;
        foreach ($this->items as $item) {
            $amount += $item->amount;
        }
        return $amount;
    }
    
    /**
     * Get the application success link.
     *
     * This link will redirect back into your application from the payment process module.
     *
     * @return string
     */
    public function getApplicationSuccessLink()
    {
        return $this->_successLink;
    }
    
    /**
     * Get the application error link.
     *
     * This link will redirect back into your application from the payment process module.
     *
     * @return string
     */
    public function getApplicationErrorLink()
    {
        return $this->_errorLink;
    }
    
    /**
     * Get the application abort link.
     *
     * This link will redirect back into your application from the payment process module.
     *
     * @return string
     */
    public function getApplicationAbortLink()
    {
        return $this->_abortLink;
    }
    
    private $_currency;

    public function setCurrency($currency)
    {
        return $this->_currency = $currency;
    }

    /**
     * Get the transaction currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->_currency;
    }

    private $_orderId;

    public function setOrderId($orderId)
    {
        $this->_orderId = $orderId;
    }
    
    /**
     * Get the transaction order id
     *
     * @return string
     */
    public function getOrderId()
    {
        return $this->_orderId;
    }
   
    private $_model;
    
    /**
     * Setter method for the Model Object.
     *
     * @param \luya\payment\models\Process $model The Data Payment ActiveRecord Model.
     */
    public function setModel(Process $model)
    {
        $this->_model = $model;
    }
    
    /**
     * Getter method for the Model Object.
     *
     * When the model is not set via the setter method first, a new Model will be created.
     *
     * @return \luya\payment\models\Process
     */
    public function getModel()
    {
        $this->checkConfig();
        if ($this->_model === null) {

            $items = $this->_items;

            if ($items === null) {
                throw new PaymentException("You have to add at least one process item with addItem().");
            }

            $model = new Process();
            $model->createTokens($this->orderId);
            $model->amount = $this->getTotalAmount();
            $model->currency = $this->currency;
            $model->order_id = $this->orderId;
            $model->success_link = $this->getApplicationSuccessLink();
            $model->error_link = $this->getApplicationErrorLink();
            $model->abort_link = $this->getApplicationAbortLink();
            $model->close_state = self::STATE_PENDING;
            $model->is_closed = 0;
            if ($model->save()) {
                foreach ($items as $item) {
                    $item->process_id = $model->id;
                    $item->save();
                }
                $this->_model = $model;
            } else {
                throw new PaymentException("Unable to save the process model. Validation failed: " . var_export($model->getErrors(), true));
            }
        }
        
        // throw exception
        return $this->_model;
    }

    private $_items;
    
    public function addItem($name, $qty, $amount)
    {
        $item = new ProcessItem();
        $item->name = $name;
        $item->qty = $qty;
        $item->amount = $amount;

        if (!$item->validate(['name', 'qty', 'amount'])) {
            throw new PaymentException("Unable to validate the item model. Validation failed: " . var_export($item->getErrors(), true));
        }

        $this->_items[] = $item;
    }

    public function setItems(array $items)
    {
        $this->_items = $items;
    }

    public function getItems()
    {
        return $this->_items;
    }

    /**
     * Returns the PaymentProcess ID.
     *
     * This value must be stored in your E-Store logic in order to find the PaymentProcess by the findByProcessId method.
     *
     * @return integer The process id value.
     */
    public function getId()
    {
        return $this->model->id;
    }
    
    /**
     * Dispatch the current controller to the getTransactionGatewayCreat link.
     *
     * @param \luya\web\Controller $controller The context controller object.
     * @throws Exception
     */
    public function dispatch(Controller $controller)
    {
        if (!$this->model) {
            throw new PaymentException("Could not dispatch the controller to the requested url as the model object is empty or contains an error.");
        }
        
        $controller->redirect($this->getTransactionGatewayCreateLink());
    }
    
    /**
     * Close the Model with a State.
     *
     * Available States:
     *
     * + PaymentProcess::STATE_SUCCESS
     * + PaymentProcess::STATE_ABORT
     * + PaymentProcess::STATE_ERROR
     *
     * @param integer $state The state to close the Model.
     * @return boolean Whether the close was sucessfull or not.
     */
    public function close($state)
    {
        $this->model->is_closed = 1;
        $this->model->close_state = $state;
        $this->model->close_timestamp = time();
        
        return $this->model->update(true, ['is_closed', 'close_state', 'close_timestamp']);
    }
    
    // static methods
    
    /**
     * Find the payment process from the ProcessId.
     *
     * @param integer $id The process ID from $this->getId() stored in your order model when dispatch the process.
     * @throws \luya\payment\PaymentException
     * @return \luya\payment\PaymentProcess Returns the PaymentProcess Object itself.
     */
    public static function findByProcessId($id)
    {
        $model = Process::find()->where(['id' => $id, 'is_closed' => 0])->with(['items'])->one();
        
        if ($model) {
            $object = Yii::createObject([
                'class' => self::class,
                'orderId' => $model->order_id,
                'currency' => $model->currency,
                'successLink' => $model->success_link,
                'errorLink' => $model->error_link,
                'abortLink' => $model->abort_link,
                'items' => $model->items,
            ]);
            $object->setModel($model);
            return $object;
        }
        
        throw new PaymentException("Unable to find the process by ID {$id}");
    }
    
    /**
     * Find a payment process based on the Token and Random Key.
     *
     * This method is used inside the payment controllers and should not be used in your application logic.
     *
     * @param string $authToken The auth token which is generated while creating the Process.
     * @param string $randomKey The random key from the database table.
     * @throws \luya\payment\PaymentException
     * @return \luya\payment\PaymentProcess Returns the PaymentProcess Object itself.
     */
    public static function findByToken($authToken, $randomKey)
    {
        $model = static::findModel($authToken, $randomKey);
    
        if ($model) {
            $object = Yii::createObject([
                'class' => self::class,
                'orderId' => $model->order_id,
                'currency' => $model->currency,
                'successLink' => $model->success_link,
                'errorLink' => $model->error_link,
                'abortLink' => $model->abort_link,
                'items' => $model->items,
            ]);
            $object->setModel($model);
            return $object;
        }
    
        throw new PaymentException("Unable to find the process by token {$authToken}");
    }
    
    private static function findModel($authToken, $randomKey)
    {
        $model = Process::find()->where(['random_key' => $randomKey, 'is_closed' => 0])->with(['items'])->one();
        if ($model) {
            $model->auth_token = $authToken;
            if ($model->validateAuthToken()) {
                return $model;
            }
        }
    
        return false;
    }
}
