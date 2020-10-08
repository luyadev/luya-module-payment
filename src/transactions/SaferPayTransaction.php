<?php

namespace luya\payment\transactions;

use Yii;
use luya\payment\base\Transaction;
use luya\payment\providers\SaferPayProvider;
use luya\payment\PaymentException;
use yii\base\InvalidConfigException;

/**
 * Safer Pay Transaction.
 *
 * Test SaferPay Transaction:
 *
 * ```php
 * 'class' => 'luya\payment\transactions\SaferPayTransaction',
 * 'accountId' => '401860-17795278',
 * 'spPassword' => '8e7Yn5yk',
 * 'mode' => 'sandbox',
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class SaferPayTransaction extends Transaction
{
    /**
     * @var string Production mode
     */
    const MODE_LIVE = 'live';

    /**
     * @var string Sandbox/Testing mode
     */
    const MODE_SANDBOX = 'sandbox';

    /**
     * @var string The accountId value from the safer pay backend.
     */
    public $accountId;
   
    /**
     * @param string Test account spPassword (from the docs: Die Übergabe des Parameters spPassword ist nur beim Testkonto erforderlich. Für produktive Konten wird
     * dieser Parameter nicht benötigt!)
     */
    public $spPassword;

    /**
     * @param string The mode which changes the urls for sandbox or live
     */
    public $mode = self::MODE_LIVE;
    
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
        return new SaferPayProvider([
            'mode' => $this->mode,
        ]);
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
        
        // the response status is 200 but the content is not a valid URL
        // therefore trhow an exception with the content. Example value could be:
        // `ERROR: Missing or wrong ACCOUNTID attribute`
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new PaymentException("Invalid URL: " . $url);
        }
        
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
                return $this->redirectApplicationSuccess();
            }
        }
        
        return $this->redirectTransactionFail();
    }
    
    /**
     * {@inheritDoc}
     */
    public function notify()
    {
        return $this->redirectApplicationSuccess();
    }
    
    /**
     * {@inheritDoc}
     */
    public function fail()
    {
        return $this->redirectApplicationError();
    }
    
    /**
     * {@inheritDoc}
     */
    public function abort()
    {
        return $this->redirectApplicationAbort();
    }
}
