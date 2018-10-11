<?php

namespace luya\payment\transaction;

use Yii;
use luya\payment\base\Transaction;
use luya\payment\base\TransactionInterface;
use luya\payment\PaymentException;
use luya\payment\provider\PayPalProvider;
use yii\base\InvalidConfigException;

/**
 * PayPal Transaction.
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class PayPalTransaction extends Transaction implements TransactionInterface
{
    const MODE_LIVE = 'live';
    const MODE_SANDBOX = 'sandbox';

    public $clientId;
    
    public $clientSecret;
    
    /**
     * @var string The mode in which the api should be called `live` or `sandbox`. Default is live. Previous knonw as `sandboxMode`.
     */
    public $mode = self::MODE_LIVE;
    
    /**
     * @var string The PayPal interface displays a name for the Amount of the ordering, this is the product text.
     */
    public $productDescription;
    
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();
        
        if ($this->clientId === null || $this->clientSecret === null) {
            throw new InvalidConfigException("the paypal clientId and clientSecret properite can not be null!");
        }
    }
    
    /**
     * Get the PayPal Provider
     *
     * @return PayPalProvider
     */
    public function getProvider()
    {
        return new PayPalProvider(['mode' => $this->mode]);
    }
    
    private function getOrderDescription()
    {
        if (empty($this->productDescription)) {
            return $this->getModel()->getOrderId();
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
        return number_format($this->getModel()->getTotalAmount() / 100, 2);
    }
    
    /**
     * {@inheritDoc}
     */
    public function create()
    {
        $url = $this->getProvider()->call('create', [
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'orderId' => $this->getModel()->getOrderId(),
            'amount' => $this->getFloatAmount(),
            'currency' => $this->getModel()->getCurrency(),
            'description' => $this->getOrderDescription(),
            'returnUrl' => $this->getModel()->getTransactionGatewayBackLink(),
            'cancelUrl' => $this->getModel()->getTransactionGatewayAbortLink(),
        ]);
        
        return $this->getContext()->redirect($url);
    }
    
    /**
     * {@inheritDoc}
     */
    public function back()
    {
        $response = $this->getProvider()->call('execute', [
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'paymentId' => Yii::$app->request->get('paymentId', false),
            'payerId' => Yii::$app->request->get('PayerID', false),
            'amount' => $this->getFloatAmount(),
            'currency' => $this->getModel()->getCurrency(),
        ]);
        
        if ($response) {
            return $this->getContext()->redirect($this->getModel()->getApplicationSuccessLink());
        }
        
        return $this->getContext()->redirect($this->getModel()->getTransactionGatewayFailLink());
    }
    
    /**
     * {@inheritDoc}
     */
    public function notify()
    {
        throw new PaymentException('PayPal notify action is not implemented.');
    }
    
    /**
     * {@inheritDoc}
     */
    public function fail()
    {
        return $this->getContext()->redirect($this->getModel()->getApplicationErrorLink());
    }
    
    /**
     * {@inheritDoc}
     */
    public function abort()
    {
        return $this->getContext()->redirect($this->getModel()->getApplicationAbortLink());
    }
}
