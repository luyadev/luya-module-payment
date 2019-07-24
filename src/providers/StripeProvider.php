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

        return $this->handleIntentResponse($intent);
    }

    public function callGenerateIntentResponse($intentId)
    {
        try {
            $intent = PaymentIntent::retrieve($intentId);
            $intent->confirm();
        } catch (Exception $exception) {
            return $this->exceptionResponse($exception);
        }

        return $this->handleIntentResponse($intent);
    }

    public function callVerifySuccessIntent($id)
    {
        return PaymentIntent::retrieve($id)->status === PaymentIntent::STATUS_SUCCEEDED;
    }

    protected function handleIntentResponse(PaymentIntent $intent)
    {
        if ($intent->status == PaymentIntent::STATUS_REQUIRES_ACTION &&
            $intent->next_action->type == 'use_stripe_sdk') {
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
        } else {
            Yii::$app->response->statusCode = 400;
            return ['error' => 'Invalid PaymentIntent status', 'status' => $intent->status];
        }
    }

    protected function exceptionResponse(Exception $exception)
    {
        Yii::$app->response->statusCode = 400;
        return ['error' => ['message' => $exception->getMessage()]];
    }
}
