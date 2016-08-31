<?php

namespace luya\payment\transaction;

use Yii;
use luya\payment\base\Transaction;
use luya\payment\base\TransactionInterface;
use luya\payment\PaymentException;
use luya\payment\provider\PayPalProvider;

class PayPalTransaction extends Transaction implements TransactionInterface
{
    public $clientId = null;
    
    public $clientSecret = null;
    
    /**
     * @var string The mode in which the api should be called `live` or `sandbox`. Default is live. Previous knonw as `sandboxMode`.
     */
    public $mode = 'live';
    
    /**
     * @var string The PayPal interface displays a name for the Amount of the ordering, this is the product text.
     */
    public $productDescription = null;
    
    public function init()
    {
        parent::init();
        
        if ($this->clientId === null || $this->clientSecret === null) {
            throw new PaymentException("the paypal clientId and clientSecret properite can not be null!");
        }
    }
    
    public function getProvider()
    {
        return new PayPalProvider(['mode' => $this->mode]);
    }
    
    private function getOrderDescription()
    {
        if (empty($this->productDescription)) {
            return $this->process->getOrderId();
        }
        
        return $this->productDescription;
    }
    
    /**
     * As all amounts are provided in cents we have to calculate them to not cents
     *
     * @param unknown $amount
     */
    private function getFloatAmount()
    {
        return number_format($this->process->getAmount() / 100, 2);
    }
    
    public function create()
    {
        $url = $this->provider->call('create', [
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'orderId' => $this->process->getOrderId(),
            'amount' => $this->getFloatAmount(),
            'currency' => $this->process->getCurrency(),
            'description' => $this->getOrderDescription(),
            'returnUrl' => $this->process->getTransactionGatewayBackLink(),
            'cancelUrl' => $this->process->getTransactionGatewayAbortLink(),
        ]);
        
        return $this->context->redirect($url);
    }
    
    public function back()
    {
        $response = $this->provider->call('execute', [
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'paymentId' => Yii::$app->request->get('paymentId', false),
            'payerId' => Yii::$app->request->get('PayerID', false),
            'amount' => $this->getFloatAmount(),
            'currency' => $this->process->getCurrency(),
        ]);
        
        if ($response) {
            return $this->context->redirect($this->process->getApplicationSuccessLink());
        }
        
        return $this->context->redirect($this->process->getTransactionGatewayFailLink());
    }
    
    public function notify()
    {
        throw new PaymentException('PayPal notify action is not implemented.');
    }
    
    public function fail()
    {
        return $this->context->redirect($this->process->getApplicationErrorLink());
    }
    
    public function abort()
    {
        return $this->context->redirect($this->process->getApplicationAbortLink());
    }
}
