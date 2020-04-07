<?php

namespace luya\payment\transactions;

use Exception;
use Yii;
use Stripe\Stripe;
use luya\payment\PaymentException;
use luya\payment\base\Transaction;
use luya\payment\providers\StripeProvider;
use luya\helpers\Html;
use yii\base\InvalidConfigException;
use luya\payment\frontend\Module;

/**
 * Stripe Transaction.
 *
 * Testing Cards:
 *
 * Visa card: 4242424242424242
 * 3D secure card: 4000000000003063
 *
 * @see 3d secure guide: https://stripe.com/docs/sources/three-d-secure
 * @see stripe elements: https://stripe.com/docs/stripe-js
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class StripeTransaction extends Transaction
{
    /**
     * @var string The publishable key from stripe web interface.
     */
    public $publishableKey;

    /**
     * @var string The secrete key from stripe web interface.
     */
    public $secretKey;

    /**
     * @var string The string for the label on the "first blue" button which opens the credit card enter dialog.
     */
    public $buttonLabel;

    /**
     * @var boolean Whether the layout of the website should be wrapped or not. If not the a black window with the payment dialog is shown.
     */
    public $layout = true;

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
        // handle incoming post requests, with body param or post data.
        if (Yii::$app->request->isPost) {
            Stripe::setApiKey($this->secretKey);
            try {
                // catch fors method id body param call
                $paymentMethodId = Yii::$app->request->getBodyParam('payment_method_id');
                if ($paymentMethodId) {
                    $response = $this->getProvider()->callGeneratePaymentMethodResponse($paymentMethodId, $this->getModel()->getTotalAmount(), $this->getModel()->getCurrency());
                    $this->getIntegrator()->saveProviderData($this->getModel(), $response);
                    return $this->getContext()->asJson($response);
                }

                // catch second intent call
                $paymentIntentId = Yii::$app->request->getBodyParam('payment_intent_id');
                if ($paymentIntentId) {
                    $intentResponse = $this->getProvider()->callGenerateIntentResponse($paymentIntentId);

                    // add this point store payment data
                    $this->getIntegrator()->saveProviderData($this->getModel(), $intentResponse);
                    return $this->getContext()->asJson($intentResponse);
                }

                // catch last post and verify the intent. Redirect to application on success.
                if ($this->getProvider()->callVerifySuccessIntent(Yii::$app->request->post('intentId'))) {
                    return $this->redirectApplicationSuccess();
                }
            } catch (Exception $e) {
                return $this->redirectTransactionFail();
            }

            return $this->redirectTransactionFail();
        }

        $html = Yii::$app->view->render('@payment/stripe/transaction', [
            'title' => $this->title ?: Module::t('stripe_header_title'),
            'color' => $this->color,
            'csrf' => Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken),
            'url' => $this->getModel()->getTransactionGatewayCreateLink(),
            'publishableKey' => $this->publishableKey,
            'abortLink' => $this->getModel()->getTransactionGatewayAbortLink(),
            'productItems' => $this->getModel()->getProductItems(),
            'taxItems' => $this->getModel()->getTaxItems(),
            'shippingItems' => $this->getModel()->getShippingItems(),
            'currency' => $this->getModel()->getCurrency(),
            'totalAmount' => $this->getModel()->getTotalAmount(),
        ]);

        if (!$this->layout) {
            return Yii::$app->view->render('@payment/stripe/layout', [
                'content' => $html,
            ]);
        }

        return $this->getContext()->renderContent($html);
    }

    /**
     * {@inheritDoc}
     */
    public function back()
    {
        throw new PaymentException("The back action is not supported for Stripe integration.");
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
