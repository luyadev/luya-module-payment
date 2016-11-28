<?php

namespace luya\payment\base;

use yii\base\Object;
use luya\payment\PaymentProcess;
use yii\web\Controller;

/**
 * Transaction Abstraction.
 *
 * Each transaction must implement the Transaction Abstraction class.
 *
 * @author Basil Suter <basil@nadar.io>
 */
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
    
    public function setContext(Controller $context)
    {
        $this->_context = $context;
    }
    
    public function getContext()
    {
        return $this->_context;
    }
}
