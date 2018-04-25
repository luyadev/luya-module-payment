<?php

namespace luya\payment;

use Yii;
use yii\base\InvalidConfigException;

/**
 * Payment Module.
 *
 * The payment module class to configure in the modules section of your config.
 *
 * ```php
 * 'modules' => [
 *     // ...
 *     'payment' => 'luya\payment\Module',
 *     // ...
 * ]
 * ```
 *
 * @property \luya\base\TransactionInterface $transaction
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Module extends \luya\base\Module
{
    public $urlRules = [
        ['pattern' => 'payment-create/<lpToken:\w+>/<lpKey:\w+>', 'route' => 'payment/default/create'],
        ['pattern' => 'payment-back/<lpToken:\w+>/<lpKey:\w+>', 'route' => 'payment/default/back'],
        ['pattern' => 'payment-fail/<lpToken:\w+>/<lpKey:\w+>', 'route' => 'payment/default/fail'],
        ['pattern' => 'payment-abort/<lpToken:\w+>/<lpKey:\w+>', 'route' => 'payment/default/abort'],
        ['pattern' => 'payment-notify/<lpToken:\w+>/<lpKey:\w+>', 'route' => 'payment/default/notify'],
    ];
    
    private $_transaction;
    
    public function getTransaction()
    {
        return $this->_transaction;
    }
    
    /**
     *
     * @var array A transaction object config array for the given provider:
     * + paypal:
     * ```php
     * 'class' => payment\transaction\PayPalTransaction::className(),
     * 'clientId' => 'ClientIdFromPayPalApplication',
     * 'clientSecret' => 'ClientSecretFromPayPalApplication',
     * 'mode' => 'live', // 'sandbox',
     * 'productDescription' => 'MyOnlineStore Order',
     * ```
     * + saferpay:
     * ```php
     * 'class' => payment\transaction\SaferPayTransaction::className(),
     * 'accountId' => 'SAFERPAYACCOUNTID', // each transaction can have specific attributes, saferpay requires an accountId',
     * ```
     */
    public function setTransaction(array $config)
    {
        $this->_transaction = Yii::createObject($config);
    }
}
