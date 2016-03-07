<?php

namespace payment\transaction;

use Yii;
use payment\base\Transaction;
use payment\base\TransactionInterface;
use payment\provider\SaferPayProvider;
use payment\PaymentException;

class SaferPayTransaction extends Transaction implements TransactionInterface
{
    public $accountId = null;
   
    public $spPassword = null;
    
    public function init()
    {
        parent::init();
        
        if (empty($this->accountId)) {
            throw new PaymentException("accountId must be set in your saferpay transaction");
        }
    }
    
    public function getProvider()
    {
        return new SaferPayProvider();
    }
    
    public function create()
    {
        $url = $this->provider->call('create', [
            'accountId' => $this->accountId,
            'amount' => $this->process->getAmount(),
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
                'amount' => $this->process->getAmount(),
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
    
    public function notify()
    {
        throw new PaymentException('SaferPay notify action is not implemented.');
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
