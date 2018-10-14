<?php

namespace luya\payment\frontend;

use Yii;
use yii\base\InvalidConfigException;
use luya\payment\integrators\DatabaseIntegrator;

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
 * @property \luya\payment\base\TransactionInterface $transaction
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Module extends \luya\base\Module
{
    /**
     * @inheritDoc
     */
    public $urlRules = [
        ['pattern' => 'payment-create/<lpToken:\w+>/<lpKey:\w+>/<time:[0-9\.]+>', 'route' => 'payment/default/create'],
        ['pattern' => 'payment-back/<lpToken:\w+>/<lpKey:\w+>/<time:[0-9\.]+>', 'route' => 'payment/default/back'],
        ['pattern' => 'payment-fail/<lpToken:\w+>/<lpKey:\w+>/<time:[0-9\.]+>', 'route' => 'payment/default/fail'],
        ['pattern' => 'payment-abort/<lpToken:\w+>/<lpKey:\w+>/<time:[0-9\.]+>', 'route' => 'payment/default/abort'],
        ['pattern' => 'payment-notify/<lpToken:\w+>/<lpKey:\w+>/<time:[0-9\.]+>', 'route' => 'payment/default/notify'],
    ];

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        
        if ($this->transaction === null) {
            throw new InvalidConfigException("The transaction property can not be null.");
        }
    }
    
    private $_transaction;
    
    /**
     * Get the transaction object
     *
     * @return \luya\payment\base\TransactionInterface
     */
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

    private $_integrator;

    public function setIntegrator(array $config)
    {
        $this->_integrator = $config;
    }

    public function getIntegrator()
    {
        if ($this->_integrator === null) {
            $this->_integrator = ['class' => DatabaseIntegrator::class];
        }
        
        if (is_array($this->_integrator)) {
            $this->_integrator = Yii::createObject($this->_integrator);
        }

        return $this->_integrator;
    }
}
