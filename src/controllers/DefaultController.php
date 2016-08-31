<?php

namespace luya\payment\controllers;

use luya\payment\PaymentProcess;

class DefaultController extends \luya\web\Controller
{
    public function actionCreate($lpToken, $lpKey)
    {
        $process = PaymentProcess::findByToken($lpToken, $lpKey);
        $process->transaction->setContext($this);
        $process->model->addEvent(__METHOD__);
        return $process->transaction->create();
    }
    
    public function actionBack($lpToken, $lpKey)
    {
        $process = PaymentProcess::findByToken($lpToken, $lpKey);
        $process->transaction->setContext($this);
        $process->model->addEvent(__METHOD__);
        return $process->transaction->back();
    }
    
    public function actionFail($lpToken, $lpKey)
    {
        $process = PaymentProcess::findByToken($lpToken, $lpKey);
        $process->transaction->setContext($this);
        $process->model->addEvent(__METHOD__);
        return $process->transaction->fail();
    }
    
    public function actionAbort($lpToken, $lpKey)
    {
        $process = PaymentProcess::findByToken($lpToken, $lpKey);
        $process->transaction->setContext($this);
        $process->model->addEvent(__METHOD__);
        return $process->transaction->abort();
    }
    
    public function actionNotify($lpToken, $lpKey)
    {
        $process = PaymentProcess::findByToken($lpToken, $lpKey);
        $process->transaction->setContext($this);
        $process->model->addEvent(__METHOD__);
        return $process->transaction->notify();
    }
}
