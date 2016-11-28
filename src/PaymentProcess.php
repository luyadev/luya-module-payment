<?php

namespace luya\payment;

use Yii;
use yii\base\Object;
use luya\helpers\Url;
use luya\Exception;
use luya\payment\base\TransactionInterface;
use luya\payment\PaymentException;
use luya\payment\models\PaymentProcess as PaymentProcessModel;
use luya\payment\base\PaymentProcessInterface;

/**
 * @property luya\luya\payment\base\TransactionInterface $transaction Contains the transaction interface
 * @property float $amount The amount to pay
 * @author nadar
 */
class PaymentProcess extends \yii\base\Object implements PaymentProcessInterface
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
    
        if (!is_array($this->transactionConfig)) {
            throw new PaymentException("transaction must be an array configuring your transaction class see Yii::createObject()");
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
                throw new PaymentException('amount is not valid');
            }
            
            $this->_amount = $value;
        }
        
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
    
    public function getAmount()
    {
        return $this->_amount;
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
    
    public function model($key)
    {
        if ($this->_model === null) {
            Yii::trace('create new model?', __METHOD__);
            $model = new PaymentProcessModel();
            $model->createTokens($key);
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
                throw new PaymentException("unable to create payment process model!");
            }
        }
        
        return $this->_model;
    }
    
    public function setModel(PaymentProcessModel $model)
    {
        $this->_model = $model;
    }
    
    public function getModel()
    {
        // throw exception
        return $this->_model;
    }
    
    public function getId()
    {
        return $this->model($this->orderId)->id;
    }
    
    public function dispatch(\yii\web\Controller $controller)
    {
        $model = $this->model($this->orderId);
        
        if (!$model) {
            throw new Exception('Payment model initializing error!');
        }
        
        $controller->redirect(Url::toRoute(['/payment/default/create', 'lpToken' => $model->auth_token, 'lpKey' => $model->random_key], true));
    }
    
    public function getTransactionGatewayBackLink()
    {
        return Url::toRoute(['/payment/default/back', 'lpToken' => $this->model->auth_token, 'lpKey' => $this->model->random_key], true);
    }
    
    public function getTransactionGatewayFailLink()
    {
        return Url::toRoute(['/payment/default/fail', 'lpToken' => $this->model->auth_token, 'lpKey' => $this->model->random_key], true);
    }
    
    public function getTransactionGatewayAbortLink()
    {
        return Url::toRoute(['/payment/default/abort', 'lpToken' => $this->model->auth_token, 'lpKey' => $this->model->random_key], true);
    }
    
    public function getTransactionGatewayNotifyLink()
    {
        return Url::toRoute(['/payment/default/notify', 'lpToken' => $this->model->auth_token, 'lpKey' => $this->model->random_key], true);
    }
    
    public function close($state)
    {
        $this->model->is_closed = 1;
        $this->model->close_state = $state;
        return $this->model->update(false);
    }
    
    public static function findById($id)
    {
        $model = PaymentProcessModel::findOne(['id' => $id, 'is_closed' => 0]);
        
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
        $model = PaymentProcessModel::findOne(['random_key' => $randomKey, 'is_closed' => 0]);
        if ($model) {
            $model->auth_token = $authToken;
            if ($model->validateAuthToken()) {
                return $model;
            }
        }
    
        return false;
    }
    
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
            $model->auth_token = $authToken;
            $object->setModel($model);
            return $object;
        }
    
        throw new PaymentException("Could not find you transaction!");
    }
}
