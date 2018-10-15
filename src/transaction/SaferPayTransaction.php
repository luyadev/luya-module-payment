<?php

namespace luya\payment\transaction;

use Yii;
use luya\payment\base\Transaction;
use luya\payment\provider\SaferPayProvider;
use luya\payment\PaymentException;
use yii\base\InvalidConfigException;

/**
 * Safer Pay Transaction.
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class SaferPayTransaction extends Transaction
{
    /**
     * @var string The accountId value from the safer pay backend.
     */
    public $accountId;
   
    public $spPassword;
    
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();
        
        if (empty($this->accountId)) {
            throw new InvalidConfigException("accountId must be set in your saferpay transaction");
        }
    }
    
    /**
     * Get the safer pay provider object.
     *
     * @return SaferPayProvider
     */
    public function getProvider()
    {
        return new SaferPayProvider();
    }
    
    /**
     * {@inheritDoc}
     */
    public function create()
    {
        $url = $this->getProvider()->call('create', [
            'accountId' => $this->accountId,
            'amount' => $this->getModel()->getTotalAmount(),
            'currency' => $this->getModel()->getCurrency(),
            'orderId' => $this->getModel()->getOrderId(),
            'description' => $this->getModel()->getOrderId(),
            'successLink' => $this->getModel()->getTransactionGatewayBackLink(),
            'failLink' => $this->getModel()->getTransactionGatewayFailLink(),
            'backLink' => $this->getModel()->getTransactionGatewayAbortLink(),
            'notifyUrl' => $this->getModel()->getTransactionGatewayNotifyLink(),
        ]);
        
        return $this->getContext()->redirect($url);
    }
    
    /**
     * {@inheritDoc}
     */
    public function back()
    {
        $signature = Yii::$app->request->get('SIGNATURE', false);
        $data = Yii::$app->request->get('DATA', false);
        
        $confirmResponse = $this->getProvider()->call('confirm', [
            'data' => $data,
            'signature' => $signature,
        ]);
        
        $parts = explode(":", $confirmResponse);
        
        if (isset($parts[0]) && $parts[0] == 'OK' && $parts[1]) {
            
            // create $TOKEN and $ID variable
            parse_str($parts[1]);
            
            $completeResponse = $this->getProvider()->call('complete', [
                'id' => $ID,
                'token' => $TOKEN,
                'amount' => $this->getModel()->getTotalAmount(),
                'action' => 'Settlement',
                'accountId' => $this->accountId,
                'spPassword' => $this->spPassword,
            ]);
            
            $completeParts = explode(":", $completeResponse);
            
            if (isset($completeParts[0]) && $completeParts[0] == 'OK') {
                return $this->getContext()->redirect($this->getModel()->getApplicationSuccessLink());
            }
        }
        
        return $this->getContext()->redirect($this->getModel()->getTransactionGatewayFailLink());
    }
    
    /**
     * {@inheritDoc}
     */
    public function notify()
    {
        return $this->getContext()->redirect($this->getModel()->getApplicationSuccessLink());
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
