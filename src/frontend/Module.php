<?php

namespace luya\payment\frontend;

use luya\payment\integrators\DatabaseIntegrator;
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
 * @property \luya\payment\base\Transaction $transaction
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
        ['pattern' => 'payment-create/<lpToken:[0-9\.\,a-zA-Z\-]+>/<lpKey:[0-9\.\,a-zA-Z\-]+>/<time:[0-9\.\,]+>', 'route' => 'payment/default/create'],
        ['pattern' => 'payment-back/<lpToken:[0-9\.\,a-zA-Z\-]+>/<lpKey:[0-9\.\,a-zA-Z\-]+>/<time:[0-9\.\,]+>', 'route' => 'payment/default/back'],
        ['pattern' => 'payment-fail/<lpToken:[0-9\.\,a-zA-Z\-]+>/<lpKey:[0-9\.\,a-zA-Z\-]+>/<time:[0-9\.\,]+>', 'route' => 'payment/default/fail'],
        ['pattern' => 'payment-abort/<lpToken:[0-9\.\,a-zA-Z\-]+>/<lpKey:[0-9\.\,a-zA-Z\-]+>/<time:[0-9\.\,]+>', 'route' => 'payment/default/abort'],
        ['pattern' => 'payment-notify/<lpToken:[0-9\.\,a-zA-Z\-]+>/<lpKey:[0-9\.\,a-zA-Z\-]+>/<time:[0-9\.\,]+>', 'route' => 'payment/default/notify'],
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
     * @return \luya\payment\base\Transaction
     */
    public function getTransaction()
    {
        return $this->_transaction;
    }

    /**
     *
     * @var array A transaction object config array for the given provider:
     * + saferpay:
     * ```php
     * 'class' => payment\transactions\SaferPayTransaction::className(),
     * 'accountId' => 'SAFERPAYACCOUNTID', // each transaction can have specific attributes, saferpay requires an accountId',
     * ```
     * + stripe
     */
    public function setTransaction(array $config)
    {
        $this->_transaction = Yii::createObject($config);
    }

    private $_integrator;

    /**
     * Setter method for integrator.
     *
     * @param array $config The configuration to use when the integrato is created (on get).
     */
    public function setIntegrator(array $config)
    {
        $this->_integrator = $config;
    }

    /**
     * Getter method for integrator.
     *
     * If there is no integrator defined trough setter method, the datbase integrator is used by default.
     *
     * @return \luya\payment\base\IntegratorInterface
     */
    public function getIntegrator()
    {
        // use default database integrator config if not defined.
        if ($this->_integrator === null) {
            $this->_integrator = ['class' => DatabaseIntegrator::class];
        }

        // create the integrator object if not done previously.
        if (is_array($this->_integrator)) {
            $this->_integrator = Yii::createObject($this->_integrator);
        }

        return $this->_integrator;
    }

    /**
     * {@inheritDoc}
     */
    public static function onLoad()
    {
        self::registerTranslation('paymentfrontend', static::staticBasePath() . '/messages', [
            'paymentfrontend' => 'paymentfrontend.php',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public static function t($message, array $params = [])
    {
        return parent::baseT('paymentfrontend', $message, $params);
    }
}
