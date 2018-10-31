<?php

namespace luya\payment\transaction;

use Yii;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\ThreeDSecure;
use Stripe\Source;
use luya\payment\PaymentException;
use luya\payment\base\Transaction;
use luya\payment\provider\StripeProvider;
use luya\helpers\Html;
use yii\base\InvalidConfigException;


/**
 * Stripe Transaction.
 * 
 * Testing Cards:
 * 
 * Visa card: 4242424242424242
 * 3D secure card: 4000000000003063	
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 * @see 3d secure guide: https://stripe.com/docs/sources/three-d-secure
 * @see stripe elements: https://stripe.com/docs/stripe-js
 */
class StripeTransaction extends Transaction
{
    public $publishableKey;

    public $secretKey;

    /**
     * @var string The string for the label on the "first blue" button which opens the credit card enter dialog.
     */
    public $buttonLabel;

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();

        if ($this->publishableKey === null || $this->secretKey === null) {
            throw new InvalidConfigException("The publishableKey and secretKey property from Stripe transaction can not be empty.");
        }
    }

    /**
     * Return the Strip Provider object.
     *
     * @return StripeProvider
     */
    public function getProvider()
    {
        return new StripeProvider();
    }

    /**
     * {@inheritDoc}
     */
    public function create()
    {
        if (Yii::$app->request->isPost) {
            // stripeCard
            $token = Yii::$app->request->post('sourceToken');
            $is3d = Yii::$app->request->post('threeDSecure');

            Stripe::setApiKey($this->secretKey);

            if ($is3d) {
                // SOurce\Create:
                $source = Source::create([
                    'amount' => $this->getModel()->getTotalAmount(),
                    'currency' => $this->getModel()->getCurrency(),
                    'type' => "three_d_secure",
                    "three_d_secure" => array(
                        "card" => $token,
                    ),
                    "redirect" => array(
                        "return_url" => $this->getModel()->getTransactionGatewayBackLink()
                    ),
                ]);

                if ($source->pending != 'chargeable') {
                    return $this->getContext()->redirect($source->redirect->url);
                }
                
            }

            try {
                $charge = Charge::create([
                    'amount' => $this->getModel()->getTotalAmount(),
                    'currency' => $this->getModel()->getCurrency(),
                    'source' => $token,
                ]);
            } catch (\Exception $e) {
                return $this->redirectTransactionFail();
            }

            if ($charge) {
                return $this->redirectApplicationSuccess();
            }
            
            return $this->redirectTransactionFail();
        }

        $html = Yii::$app->view->render('@payment/stripe/transaction', [
            'csrf' => Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken),
            'url' => $this->getModel()->getTransactionGatewayCreateLink(),
            'publishableKey' => $this->publishableKey,
            'abortLink' => $this->getModel()->getTransactionGatewayAbortLink()
        ]);

        return $html;

        //return $this->getContext()->renderContent($html);
    }
    
    /**
     * {@inheritDoc}
     */
    public function back()
    {
        // 3d secure get params
        // @see https://stripe.com/docs/sources/three-d-secure#customer-action
        $sourceTokenId = Yii::$app->request->get('source');
        $livemode = Yii::$app->request->get('livemode');
        $clientSecret = Yii::$app->request->get('client_secret');

        if ($sourceTokenId) {
            Stripe::setApiKey($this->secretKey);
            try {
                $charge = Charge::create([
                    'amount' => $this->getModel()->getTotalAmount(),
                    'currency' => $this->getModel()->getCurrency(),
                    'source' => $sourceTokenId,
                ]);
            } catch (\Exception $e) {
                return $this->redirectTransactionFail();
            }

            if (!$charge) {
                return $this->redirectTransactionFail();
            }
        }

        return $this->redirectApplicationSuccess();
    }
    
    /**
     * {@inheritDoc}
     */
    public function notify()
    {
        throw new PaymentException("The notify action is not supported for Stripe integration.");
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