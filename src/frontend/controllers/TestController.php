<?php

namespace luya\payment\frontend\controllers;

use Yii;
use luya\helpers\Url;
use luya\payment\PaymentProcess;
use luya\payment\transactions\SaferPayTransaction;
use luya\payment\transactions\PayPalTransaction;
use luya\payment\Pay;
use yii\filters\HttpCache;
use luya\payment\PaymentException;

class TestController extends \luya\web\Controller
{
    /**
     * Disable cache
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        $behaviors[] = [
            'class' => HttpCache::class,
            'cacheControlHeader' => 'no-store, no-cache',
            'lastModified' => function ($action, $params) {
                return time();
            },
        ];
        
        return $behaviors;
    }

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
        $process->setTotalAmount(1000);

        // prepare the order and store the process->getId()
        // ....

        Yii::$app->session->set('storeTransactionId', $process->getId());

        return $process->dispatch($this);
    }
    
    public function actionTestSuccess()
    {
        if (!Pay::isSuccess(Yii::$app->session->get('storeTransactionId', 0))) {
            throw new PaymentException("Error, invalid success payment process.");
        }

        // create order for customer ...
        // ...


        return 'success!';
    }
    
    public function actionTestError()
    {
        return 'Rendering: error action...';
    }
    
    public function actionTestAbort()
    {
        return 'Rendering: abort action...';
    }
}
