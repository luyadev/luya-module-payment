<?php

namespace luya\payment\providers;

use Exception;
use Yii;
use luya\payment\base\Provider;
use Stripe\PaymentIntent;

/**
 * Strip Provider
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class StripeProvider extends Provider
{
    public function getId()
    {
        return 'stripe';
    }

    /**
     * Generate the repsponse for payment method creation.
     *
     * @param integer $paymentMethodId
     * @param integer $totalAmount
     * @param string $currency
     * @return array
     * @since 1.1.0
     */
    public function callGeneratePaymentMethodResponse($paymentMethodId, $totalAmount, $currency)
    {
        try {
            $intent = PaymentIntent::create([
                'payment_method' => $paymentMethodId,
                'amount' => $totalAmount,
                'currency' => $currency,
                'confirmation_method' => 'manual',
                'confirm' => true,
            ]);
        } catch (Exception $exception) {
            return $this->exceptionResponse($exception);
        }

        return $this->intentResponse($intent);
    }

    /**
     * Generate the reponse for the intent call
     *
     * @param integer $intentId
     * @return array
     * @since 1.1.0
     */
    public function callGenerateIntentResponse($intentId)
    {
        try {
            $intent = PaymentIntent::retrieve($intentId);
            $intent->confirm();
        } catch (Exception $exception) {
            return $this->exceptionResponse($exception);
        }

        return $this->intentResponse($intent);
    }

    /**
     * Verify if a given intent id whether its succesfull or not.
     *
     * @param integer $id
     * @return boolean
     * @since 1.1.0
     */
    public function callVerifySuccessIntent($id)
    {
        try {
            return PaymentIntent::retrieve($id)->status === PaymentIntent::STATUS_SUCCEEDED;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Generate an arrayble response for a payment intent object.
     *
     * @param PaymentIntent $intent
     * @return array
     * @since 1.1.0
     */
    protected function intentResponse(PaymentIntent $intent)
    {
        if ($intent->status == PaymentIntent::STATUS_REQUIRES_ACTION && $intent->next_action->type == 'use_stripe_sdk') {
            # Tell the client to handle the action
            return [
                'requires_action' => true,
                'payment_intent_client_secret' => $intent->client_secret
            ];
        } elseif ($intent->status == PaymentIntent::STATUS_SUCCEEDED) {
            # The payment didn’t need any additional actions and completed!
            # Handle post-payment fulfillment
            return [
                'success' => true,
                'id' => $intent->id,
            ];
        }

        return $this->exceptionResponse(new Exception("Invalid PaymentIntent status: {$intent->status}"));
    }

    /**
     * Turn an exception into an arraytable response
     *
     * @param Exception $exception
     * @return array
     * @since 1.1.0
     */
    protected function exceptionResponse(Exception $exception)
    {
        Yii::$app->response->statusCode = 400;

        return [
            'error' => [
                'message' => $exception->getMessage(),
            ]
        ];
    }
}
