<?php

namespace luya\payment\transactions;

use luya\payment\base\Transaction;
use luya\payment\PaymentException;
use luya\payment\providers\SaferPayProvider;

/**
 * @see https://saferpay.github.io/jsonapi/#ChapterPaymentPage
 * @since 3.0
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
     * @var string The mode in which the api should be called `live` or `sandbox`. Default is live. Previous knonw as `sandboxMode`.
     */
    public $mode = self::MODE_LIVE;

    /**
     * @var number A numeric value with 8 digits `12345678` 
     */
    public $terminalId;

    /**
     * @var number A numeric value `123456`
     */
    public $customerId;

    /**
     * @var string The API Key username, starts with `API_...`
     */
    public $username;

    /**
     * @var string The API Token (password), starts with `JsonApiPwed.....`
     */
    public $password;

    /**
     * Undocumented function
     *
     * @return SaferPayProvider
     */
    public function getProvider()
    {
        return new SaferPayProvider([
            'transaction' => $this,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function create()
    {
        $response = $this->getProvider()->initialize($this->getModel()->getOrderId() . uniqid(), $this->getModel(), "foobar description");

        $this->getIntegrator()->saveProviderData($this->getModel(), ['initialize' => $response]);

        if (isset($response['RedirectUrl'])) {
            return $this->getContext()->redirect($response['RedirectUrl']);
        }

        throw new PaymentException("Invalid payment transaction response, missing redirect URL.");
    }

    /**
     * {@inheritDoc}
     */
    public function back()
    {
        if ($this->assertAndCapture()) {
            return $this->redirectApplicationSuccess();
        }

        return $this->redirectTransactionFail();
    }

    /**
     * {@inheritDoc}
     */
    public function notify()
    {
        if ($this->assertAndCapture()) {
            return $this->curlApplicationLink($this->getModel()->getApplicationSuccessLink());
        }
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

    /**
     * Assert and Capture the Payment
     *
     * @return boolean Either the assert and captured returnued true or not.
     */
    private function assertAndCapture()
    {
        $data = $this->getIntegrator()->getProviderData($this->getModel());

        if (!isset($data['initialize']['Token'])) {
            throw new PaymentException('Response token is missing for initalizing call (create).');
        }

        $assert = $this->getProvider()->assert($this->getModel()->getOrderId() . uniqid(), $data['initialize']['Token']);

        $data['assert'] = $assert;

        $this->getIntegrator()->saveProviderData($this->getModel(), $data);

        if (!isset($assert['Transaction']['Id'])) {
            throw new PaymentException('Assert response has missing transaction id.');
        }

        // capture
        $capture = $this->getProvider()->capture($this->getModel()->getOrderId() . uniqid(), $assert['Transaction']['Id']);

        $data['capture'] = $capture;

        $this->getIntegrator()->saveProviderData($this->getModel(), $data);

        if (!isset($capture['Status'])) {
            throw new PaymentException("Caputre resposne has missing Status information.");
        }
        return $capture['Status'] == 'CAPTURED';
    }
}