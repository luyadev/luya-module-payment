<?php

namespace payment\controllers;

use Yii;
use luya\helpers\Url;
use payment\PaymentProcess;
use payment\transaction\SaferPayTransaction;
use payment\transaction\PayPalTransaction;

class TestController extends \luya\web\Controller
{
    public function actionIndex()
    {
        if (YII_ENV_DEV && YII_DEBUG) {
            $process = new PaymentProcess([
                'amount' => 200, // in cent
                'orderId' => 'Order-'.uniqid(),
                'currency' => 'CHF',
                'successLink' => Url::toRoute(['/payment/test/test-success'], true), // user has paid successfull
                'errorLink' => Url::toRoute(['/payment/test/test-error'], true), // user got a payment error
                'abortLink' => Url::toRoute(['/payment/test/test-abort'], true), // user has pushed the back button
                'transactionConfig' => [
    
                    /*
                     'class' => PayPalTransaction::className(),
                     'clientId' => '<CLIENTID>',
                     'clientSecret' => '<CLIENTSECRET>',
                    */
    
                    /*
                     'class' => SaferPayTransaction::className(),
                     'accountId' => '<ACCOUNTID>',
                     'spPassword' => '<SPPASSWORD>',
                    */
    
                ],
            ]);
    
            Yii::$app->session->set('storeTransactionId', $process->getId());
    
            return $process->dispatch($this);
        }
    }
    
    public function actionTestSuccess()
    {
        if (YII_ENV_DEV && YII_DEBUG) {
            $process = PaymentProcess::findById(Yii::$app->session->get('storeTransactionId', 0));
    
            // create order for customer ...
            // ...
    
            $process->close(PaymentProcess::STATE_SUCCESS);
    
            return 'success!';
        }
    }
    
    public function actionTestError()
    {
        if (YII_ENV_DEV && YII_DEBUG) {
            $process = PaymentProcess::findById(Yii::$app->session->get('storeTransactionId', 0));
    
            // display error for payment
    
            $process->close(PaymentProcess::STATE_ERROR);
    
            return 'error!';
        }
    }
    
    public function actionTestAbort()
    {
        if (YII_ENV_DEV && YII_DEBUG) {
            $process = PaymentProcess::findById(Yii::$app->session->get('storeTransactionId', 0));
    
            // redirect the user back to where he can choose another payment.
    
            $process->close(PaymentProcess::STATE_ABORT);
    
            return 'abort/stop button!';
        }
    }
}