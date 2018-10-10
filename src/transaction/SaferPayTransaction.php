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
        $url = $this->provider->call('create', [
            'accountId' => $this->accountId,
            'amount' => $this->process->getTotalAmount(),
            'currency' => $this->process->getCurrency(),
            'orderId' => $this->process->getOrderId(),
            'description' => $this->process->getOrderId(),
            'successLink' => $this->process->getTransactionGatewayBackLink(),
            'failLink' => $this->process->getTransactionGatewayFailLink(),
            'backLink' => $this->process->getTransactionGatewayAbortLink(),
            'notifyUrl' => $this->process->getTransactionGatewayNotifyLink(),
        ]);
        
        return $this->context->redirect($url);
    }
    
    /**
     * {@inheritDoc}
     */
    public function back()
    {
        $signature = Yii::$app->request->get('SIGNATURE', false);
        $data = Yii::$app->request->get('DATA', false);
        
        $confirmResponse = $this->provider->call('confirm', [
            'data' => $data,
            'signature' => $signature,
        ]);
        
        $parts = explode(":", $confirmResponse);
        
        if (isset($parts[0]) && $parts[0] == 'OK' && $parts[1]) {
            
            // create $TOKEN and $ID variable
            parse_str($parts[1]);
            
            $completeResponse = $this->provider->call('complete', [
                'id' => $ID,
                'token' => $TOKEN,
                'amount' => $this->process->getTotalAmount(),
                'action' => 'Settlement',
                'accountId' => $this->accountId,
                'spPassword' => $this->spPassword,
            ]);
            
            $completeParts = explode(":", $completeResponse);
            
            if (isset($completeParts[0]) && $completeParts[0] == 'OK') {
                return $this->context->redirect($this->process->getApplicationSuccessLink());
            }
        }
        
        return $this->context->redirect($this->process->getTransactionGatewayFailLink());
    }
    
    /**
     * {@inheritDoc}
     */
    public function notify()
    {
        return $this->context->redirect($this->process->getApplicationSuccessLink());
    }
    
    /**
     * {@inheritDoc}
     */
    public function fail()
    {
        return $this->context->redirect($this->process->getApplicationErrorLink());
    }
    
    /**
     * {@inheritDoc}
     */
    public function abort()
    {
        return $this->context->redirect($this->process->getApplicationAbortLink());
    }
}
