<?php

namespace luya\payment\controllers;

use luya\payment\PaymentProcess;

/**
 * Default Payment Controller.
 *
 * This controller handles the internal payment process and transactions.
 *
 * @property \luya\payment\Module $module
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class DefaultController extends \luya\web\Controller
{
    public function actionCreate($lpToken, $lpKey)
    {
        $process = PaymentProcess::findByToken($lpToken, $lpKey);
        $process->model->addPaymentTraceEvent(__METHOD__);
        
        $this->module->transaction->setProcess($process);
        $this->module->transaction->setContext($this);
        
        return $this->module->transaction->create();
    }
    
    public function actionBack($lpToken, $lpKey)
    {
        $process = PaymentProcess::findByToken($lpToken, $lpKey);
        $process->model->addPaymentTraceEvent(__METHOD__);
        
        $this->module->transaction->setProcess($process);
        $this->module->transaction->setContext($this);
        
        return $this->module->transaction->back();
    }
    
    public function actionFail($lpToken, $lpKey)
    {
        $process = PaymentProcess::findByToken($lpToken, $lpKey);
        $process->model->addPaymentTraceEvent(__METHOD__);
        
        $this->module->transaction->setProcess($process);
        $this->module->transaction->setContext($this);
        
        return $this->module->transaction->fail();
    }
    
    public function actionAbort($lpToken, $lpKey)
    {
        $process = PaymentProcess::findByToken($lpToken, $lpKey);
        $process->model->addPaymentTraceEvent(__METHOD__);
        
        $this->module->transaction->setProcess($process);
        $this->module->transaction->setContext($this);
        
        return $this->module->transaction->abort();
    }
    
    public function actionNotify($lpToken, $lpKey)
    {
        $process = PaymentProcess::findByToken($lpToken, $lpKey);
        $process->model->addPaymentTraceEvent(__METHOD__);
        
        $this->module->transaction->setProcess($process);
        $this->module->transaction->setContext($this);
        
        return $this->module->transaction->notify();
    }
}
