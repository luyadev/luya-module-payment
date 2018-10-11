<?php

namespace luya\payment\frontend\controllers;

use Yii;
use luya\helpers\Url;
use luya\payment\PaymentProcess;
use luya\payment\transaction\SaferPayTransaction;
use luya\payment\transaction\PayPalTransaction;
use luya\payment\Pay;

class TestController extends \luya\web\Controller
{
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if (YII_ENV_DEV && YII_DEBUG) {
                return true;
            }
        }

        return false;
    }

    public function actionIndex()
    {
        Yii::$app->session->remove('storeTransactionId');

        $process = new Pay();
        $process->setOrderId('order-'.uniqid());
        $process->setCurrency('CHF');
        $process->setSuccessLink(['/payment/test/test-success']);
        $process->setErrorLink(['/payment/test/test-error']);
        $process->setAbortLink(['/payment/test/test-abort']);
        $process->addItem('Product 1', 1, 200);
        $process->addItem('Product 2', 2, 400);

        // prepare the order and store the process->getId()
        // ....

        Yii::$app->session->set('storeTransactionId', $process->getId());

        return $process->dispatch($this);
    }
    
    public function actionTestSuccess()
    {
        $id = Pay::close(Yii::$app->session->get('storeTransactionId', 0), Pay::STATE_SUCCESS);

        // create order for customer ...
        // ...


        return 'success!';
    }
    
    public function actionTestError()
    {
        $id = Pay::close(Yii::$app->session->get('storeTransactionId', 0), Pay::STATE_ERROR);
        // display error for payment mark order as failed
        // ....


        return 'error!';
    }
    
    public function actionTestAbort()
    {
        $id = Pay::close(Yii::$app->session->get('storeTransactionId', 0), Pay::STATE_ABORT);

        // redirect the user back to where he can choose another payment and mark order as aborted/failed.
        // ...


        return 'abort/stop!';
    }
}
