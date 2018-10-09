<?php

namespace luya\payment\controllers;

use Yii;
use luya\helpers\Url;
use luya\payment\PaymentProcess;
use luya\payment\transaction\SaferPayTransaction;
use luya\payment\transaction\PayPalTransaction;

class TestController extends \luya\web\Controller
{
    public function actionIndex()
    {
        Yii::$app->session->removeAll();
        
        if (YII_ENV_DEV && YII_DEBUG) {
            $process = new PaymentProcess([
                'amount' => 200, // in cent
                'orderId' => 'Order-'.uniqid(),
                'currency' => 'CHF',
                'successLink' => ['/payment/test/test-success'], // user has paid successfull
                'errorLink' => ['/payment/test/test-error'], // user got a payment error
                'abortLink' => ['/payment/test/test-abort'], // user has pushed the back button
            ]);
    
            Yii::$app->session->set('storeTransactionId', $process->getId());
    
            return $process->dispatch($this);
        }
    }
    
    public function actionTestSuccess()
    {
        if (YII_ENV_DEV && YII_DEBUG) {
            $process = PaymentProcess::findByProcessId(Yii::$app->session->get('storeTransactionId', 0));
    
            // create order for customer ...
            // ...

            $process->close(PaymentProcess::STATE_SUCCESS);
    
            return 'success!';
        }
    }
    
    public function actionTestError()
    {
        if (YII_ENV_DEV && YII_DEBUG) {
            $process = PaymentProcess::findByProcessId(Yii::$app->session->get('storeTransactionId', 0));
    
            // display error for payment

            $process->close(PaymentProcess::STATE_ERROR);
    
            return 'error!';
        }
    }
    
    public function actionTestAbort()
    {
        if (YII_ENV_DEV && YII_DEBUG) {
            $process = PaymentProcess::findByProcessId(Yii::$app->session->get('storeTransactionId', 0));
    
            // redirect the user back to where he can choose another payment.

            $process->close(PaymentProcess::STATE_ABORT);
    
            return 'abort/stop button!';
        }
    }
}
