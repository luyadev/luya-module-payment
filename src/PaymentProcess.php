<?php

namespace luya\payment;

use Yii;
use yii\base\Object;
use yii\web\Controller;
use luya\helpers\Url;
use luya\Exception;
use luya\payment\base\TransactionInterface;
use luya\payment\PaymentException;
use luya\payment\models\DataPaymentProcessModel;
/**
 * PaymentProcess.
 * 
 * @property \luya\payment\base\TransactionInterface $transaction Contains the transaction interface
 * @property \luya\payment\models\DataPaymentProcessModel $model The DataPaymentProcessModel
 * @property float $amount The amount to pay
 * @property integer $id Returns the Process ID to store in your E-Store logic.
 * 
 * @author Basil Suter <basil@nadar.io>
 */
final class PaymentProcess extends Object
{
    const STATE_SUCCESS = 1;
    
    const STATE_ERROR = 2;
    
    const STATE_ABORT = 3;
    
    public $orderId = null;
    
    public $currency = null;
    
    public $successLink = null;
    
    public $errorLink = null;
    
    public $abortLink = null;
    
    public $transactionConfig = null;
    
    public function init()
    {
        parent::init();
    
        if ($this->transactionConfig === null) {
            throw new PaymentException("The transactionConfig property can not be empty, you have to provide a transaction class to configure.");
        }
    
        if (empty($this->amount) || empty($this->orderId) || empty($this->currency) || empty($this->successLink) || empty($this->errorLink) || empty($this->abortLink)) {
            throw new PaymentException("amount, orderId, currency, successLink, errorLink and abortLink properties can not be null!");
        }
    }
    
    private $_transaction = null;
    
    public function getTransaction()
    {
        if ($this->_transaction === null) {
            $this->_transaction = Yii::createObject($this->transactionConfig);
            $this->_transaction->setProcess($this);
        }
        
        return $this->_transaction;
    }
    
    private $_amount = null; // setter and getter

    public function setAmount($value)
    {
        if ($this->_amount === null) {
            if (!is_numeric($value)) {
                throw new PaymentException('The amount property must be an numeric value.');
            }
            
            $this->_amount = $value;
        }
        
        return $this->_amount;
    }
    
    public function getAmount()
    {
        return $this->_amount;
    }
    
    public function getApplicationSuccessLink()
    {
        return $this->successLink;
    }
    
    public function getApplicationErrorLink()
    {
        return $this->errorLink;
    }
    
    public function getApplicationAbortLink()
    {
        return $this->abortLink;
    }
    
    public function getCurrency()
    {
        return $this->currency;
    }
    
    public function getOrderId()
    {
        return $this->orderId;
    }
   
    private $_model = null;
    
    /**
     * Setter method for the Model Object.
     * 
     * @param \luya\payment\models\DataPaymentProcessModel $model The Data Payment ActiveRecord Model.
     */
    public function setModel(DataPaymentProcessModel $model)
    {
        $this->_model = $model;
    }
    
    /**
     * Getter method for the Model Object.
     * 
     * When the model is not set via the setter method first, a new Model will be created.
     * 
     * @return \luya\payment\models\DataPaymentProcessModel
     */
    public function getModel()
    {
        if ($this->_model === null) {
            $model = new DataPaymentProcessModel();
            $model->createTokens($this->orderId);
            $model->attributes = [
                'amount' => $this->amount,
                'currency' => $this->currency,
                'order_id' => $this->orderId,
                'provider_name' => $this->transaction->provider->id,
                'success_link' => $this->successLink,
                'error_link' => $this->errorLink,
                'abort_link' => $this->abortLink,
                'transaction_config' => $this->transactionConfig,
            ];
            if ($model->save()) {
                $this->_model = $model;
            } else {
                throw new PaymentException("Unable to save the DataPaymentProcessModel, validation failed during save process.");
            }
        }
        
        // throw exception
        return $this->_model;
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
    
    public function dispatch(Controller $controller)
    {
        $model = $this->model;
        
        if (!$model) {
            throw new Exception('Payment model initializing error!');
        }
        
        $controller->redirect(Url::toInternal(['/payment/default/create', 'lpToken' => $model->auth_token, 'lpKey' => $model->random_key], true));
    }
    
    public function getTransactionGatewayBackLink()
    {
        return Url::toInternal(['/payment/default/back', 'lpToken' => $this->model->auth_token, 'lpKey' => $this->model->random_key], true);
    }
    
    public function getTransactionGatewayFailLink()
    {
        return Url::toInternal(['/payment/default/fail', 'lpToken' => $this->model->auth_token, 'lpKey' => $this->model->random_key], true);
    }
    
    public function getTransactionGatewayAbortLink()
    {
        return Url::toInternal(['/payment/default/abort', 'lpToken' => $this->model->auth_token, 'lpKey' => $this->model->random_key], true);
    }
    
    public function getTransactionGatewayNotifyLink()
    {
        return Url::toInternal(['/payment/default/notify', 'lpToken' => $this->model->auth_token, 'lpKey' => $this->model->random_key], true);
    }
    
    public function close($state)
    {
        $this->model->is_closed = 1;
        $this->model->close_state = $state;
        return $this->model->update(false);
    }
    
    // static methods
    
    /**
     * Find the process by the process Id.
     * 
     * **Deprecated use findByProcessId() instead**.
     * 
     * @deprecated Will be removed in version 1.0.0 use findByProcessId() instead.
     * @param integer $id The process ID from $this->getId() stored in your order model when dispatch the process.
     */
    public static function findById($id)
    {
        return static::findByProcessId($id);
    }
    
    /**
     * Find the payment process from the ProcessId.
     * 
     * @param integer $id The process ID from $this->getId() stored in your order model when dispatch the process.
     * @throws \luya\payment\PaymentException
     * @return \luya\payment\PaymentProcess Returns the PaymentProcess Object itself.
     */
    public static function findByProcessId($id)
    {
        $model = DataPaymentProcessModel::findOne(['id' => $id, 'is_closed' => 0]);
        
        if ($model) {
            $object = Yii::createObject([
                'class' => self::className(),
                'amount' => $model->amount,
                'orderId' => $model->order_id,
                'currency' => $model->currency,
                'successLink' => $model->success_link,
                'errorLink' => $model->error_link,
                'abortLink' => $model->abort_link,
                'transactionConfig' => $model->transaction_config,
            ]);
            $object->setModel($model);
            return $object;
        }
        
        throw new PaymentException("Could not find you transaction!");
    }
    
    /**
     * Find a payment process based on the Token and Random Key.
     * 
     * This method is used inside the payment controllers and should not be used in your application logic.
     * 
     * @param string $authToken The auth token which is generated while creating the DataPaymentProcessModel.
     * @param string $randomKey The random key from the database table.
     * @throws \luya\payment\PaymentException
     * @return \luya\payment\PaymentProcess Returns the PaymentProcess Object itself.
     */
    public static function findByToken($authToken, $randomKey)
    {
        $model = static::findModel($authToken, $randomKey);
    
        if ($model) {
            $object = Yii::createObject([
                'class' => self::className(),
                'amount' => $model->amount,
                'orderId' => $model->order_id,
                'currency' => $model->currency,
                'successLink' => $model->success_link,
                'errorLink' => $model->error_link,
                'abortLink' => $model->abort_link,
                'transactionConfig' => $model->transaction_config,
            ]);
            $object->setModel($model);
            return $object;
        }
    
        throw new PaymentException("Could not find you transaction!");
    }
    
    private static function findModel($authToken, $randomKey)
    {
        $model = DataPaymentProcessModel::findOne(['random_key' => $randomKey, 'is_closed' => 0]);
        if ($model) {
            $model->auth_token = $authToken;
            if ($model->validateAuthToken()) {
                return $model;
            }
        }
    
        return false;
    }
}
