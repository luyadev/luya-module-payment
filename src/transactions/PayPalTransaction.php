<?php

namespace luya\payment\transactions;

use luya\payment\base\Transaction;
use luya\payment\PaymentException;
use luya\payment\providers\PayPalProvider;
use Yii;
use yii\base\InvalidConfigException;

/**
 * PayPal Transaction.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class PayPalTransaction extends Transaction
{
    /**
     * @var string Production mode
     */
    public const MODE_LIVE = 'live';

    /**
     * @var string Sandbox/Testing mode
     */
    public const MODE_SANDBOX = 'sandbox';

    /**
     * @var string The client id
     */
    public $clientId;

    /**
     * @var string the Client secret.
     */
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
        return new PayPalProvider([
            'mode' => $this->mode,
        ]);
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
    public static function floatAmount($value)
    {
        return number_format($value / 100, 2, '.', '');
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
            'amount' => $this->getModel()->getTotalAmount(),
            'currency' => $this->getModel()->getCurrency(),
            'description' => $this->getOrderDescription(),
            'returnUrl' => $this->getModel()->getTransactionGatewayBackLink(),
            'cancelUrl' => $this->getModel()->getTransactionGatewayAbortLink(),
            // add new items informations
            'items' => $this->getModel()->getProductItems(),
            'taxes' => $this->getModel()->getTaxItems(),
            'shipping' => $this->getModel()->getShippingItems(),
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
            'amount' => $this->getModel()->getTotalAmount(),
            'currency' => $this->getModel()->getCurrency(),
        ]);

        if ($response) {
            return $this->redirectApplicationSuccess();
        }

        return $this->redirectTransactionFail();
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
