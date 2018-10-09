<?php

namespace luya\payment\transaction;

use Yii;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\ThreeDSecure;
use luya\payment\PaymentException;
use luya\payment\base\Transaction;
use luya\payment\provider\StripeProvider;
use luya\helpers\Html;


/**
 * 
 * Props:
 * 
 * Test visa card: 4242424242424242
 * 3D secure card: 4000000000003063	
 */
class StripeTransaction extends Transaction
{
    public $publishableKey;

    public $secretKey;

    public function getProvider()
    {
        return new StripeProvider();
    }
    /**
     * Creates the transaction and mostly redirects to the provider afterwards
     */
    public function create()
    {
        $url = $this->getProcess()->getTransactionGatewayBackLink();
        $csrf = Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken);
        $html = <<<EOT
        <form action="$url" method="post">
        $csrf
        <script
          src="https://checkout.stripe.com/checkout.js" class="stripe-button"
          data-key="{$this->publishableKey}"
          data-amount="{$this->getProcess()->getAmount()}"
          data-locale="auto"
          data-name="LUYA PAYMENT"
          data-description="Payment description"
          data-image="https://api.heartbeat.gmbh/image/logo-heartbeat-gmbh_ea057f17.png"
          data-label="Jetzt bezahlen"
          data-zip-code="false">
        </script>
      </form>
EOT;

        $html .= Html::a('Abbrechen und Zurück', $this->getProcess()->getTransactionGatewayAbortLink());
        return $html;
    }
    
    /**
     * Return from create into the back
     */
    public function back()
    {
        $token = Yii::$app->request->post('stripeToken');
        $email = Yii::$app->request->post('stripeEmail');

        Stripe::setApiKey($this->secretKey);

        $customer = Customer::create([
            'email' => $email,
            'source' => $token,
        ]);

        try {
            $three_d_secure = ThreeDSecure::create([
                'customer' => $customer->id,
                'amount' => $this->getProcess()->getAmount(),
                'currency' => $this->getProcess()->getCurrency(),
                'return_url' => $this->getProcess()->getTransactionGatewayAbortLink(),
            ]);

            var_dump($three_d_secure);
            exit;
        } catch (\Exception $e) {
            var_dump($three_d_secure, $e->getMessage());
            exit;
        }

        try {
            $charge = Charge::create([
                /*
                'receipt_email' => '',
                */
                'customer' => $customer->id,
                'amount' => $this->getProcess()->getAmount(),
                'currency' => $this->getProcess()->getCurrency(),
            ]);
        } catch (\Exception $e) {
            var_dump($charge, $e->getMessage());
            exit;
        }

        if ($charge) {
            return $this->getContext()->redirect($this->getProcess()->getApplicationSuccessLink());
        }

        return $this->getContext()->redirect($this->getProcess()->getApplicationErrorLink());
    }
    
    /**
     * Some providers provide a notify link
     */
    public function notify()
    {
        throw new PaymentException("The notify action is not supported for Stripe integration.");
    }
    
    /**
     * An error/failure happend
     */
    public function fail()
    {
        return $this->getContext()->redirect($this->getProcess()->getApplicationErrorLink());
    }
    
    /**
     * All providers provide an abort/stop link to back into the onlinestore and choose
     */
    public function abort()
    {
        return $this->getContext()->redirect($this->getProcess()->getApplicationAbortLink());
    }
}