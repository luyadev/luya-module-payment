<?php

namespace luya\payment\base;

use luya\payment\PaymentProcess;
use yii\web\Controller;
use yii\base\BaseObject;

/**
 * Transaction Abstraction.
 *
 * Each transaction must implement the Transaction Abstraction class.
 *
 * @author Basil Suter <basil@nadar.io>
 */
abstract class Transaction extends BaseObject implements TransactionInterface
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
