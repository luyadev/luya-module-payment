<?php

namespace luya\payment\tests\providers;

use luya\payment\tests\BasePaymentTestCase;
use luya\payment\providers\StripeProvider;
use Stripe\PaymentIntent;
use Stripe\StripeObject;

class StripeProviderTest extends BasePaymentTestCase
{
    public function testGeneratePaymentResponse()
    {
        $provider = new StripeProvider();
        $response = $provider->callGeneratePaymentMethodResponse(0, 100, 'USD');
        $this->assertArrayHasKey('error', $response);
    }

    public function testGenerateIntentResponse()
    {
        $provider = new StripeProvider();
        $response = $provider->callGenerateIntentResponse(0);
        $this->assertArrayHasKey('error', $response);
    }

    public function testVerifySuccessIntent()
    {
        $provider = new StripeProvider();
        $response = $provider->callVerifySuccessIntent(0);
        
        $this->assertFalse($response);
    }

    public function testIntentResponse()
    {
        $provider = new StripeProvider();

        $successIntent = new PaymentIntent(1);
        $successIntent->status = PaymentIntent::STATUS_SUCCEEDED;
        $this->assertSame(['success' => true, 'id' => 1], $this->invokeMethod($provider, 'intentResponse', [$successIntent]));

        $failIntent = new PaymentIntent(1);
        $failIntent->status = PaymentIntent::STATUS_PROCESSING;
        $this->assertSame(['error' => ['message' => 'Invalid PaymentIntent status: processing']], $this->invokeMethod($provider, 'intentResponse', [$failIntent]));

        $actionIntent = new PaymentIntent(1);
        $actionIntent->status = PaymentIntent::STATUS_REQUIRES_ACTION;
        $actionIntent->client_secret = 'secret';
        $actionIntent->next_action = (object) ['type' => 'use_stripe_sdk'];
        $this->assertSame(['requires_action' => true, 'payment_intent_client_secret' => 'secret'], $this->invokeMethod($provider, 'intentResponse', [$actionIntent]));
    }
}