<?php

namespace luya\payment\tests\providers;

use luya\payment\tests\BasePaymentTestCase;
use luya\payment\providers\StripeProvider;

class StripeProviderTest extends BasePaymentTestCase
{
    public function testGeneratePaymentResponse()
    {
        $provider = new StripeProvider();
        $response = $provider->callGenerateIntentResponse(0);
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
}