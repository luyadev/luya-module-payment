<?php

namespace luya\payment\base;

use yii\base\Object;
use luya\payment\PaymentProcess;

abstract class Transaction extends Object implements TransactionInterface
{
    private $_process = null;
    
    public function setProcess(PaymentProcess $process)
    {
        $this->_process = $process;
    }
    
    public function getProcess()
    {
        return $this->_process;
    }
    
    private $_context = null;
    
    public function setContext(\yii\web\Controller $context)
    {
        $this->_context = $context;
    }
    
    public function getContext()
    {
        return $this->_context;
    }
}
