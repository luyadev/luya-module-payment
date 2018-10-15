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
    private $_model;
    
    /**
     * Setter method for payment process.
     *
     * @param PaymentProcess $process
     */
    public function setModel(PayModel $model)
    {
        $this->_model = $model;
    }
    
    public function getModel()
    {
        return $this->_model;
    }
    
    private $_context = null;
    
    /**
     * Setter method for controller context.
     *
     * @param Controller $context
     */
    public function setContext(Controller $context)
    {
        $this->_context = $context;
    }
    
    /**
     * Getter method for context.
     *
     * @return Controller
     */
    public function getContext()
    {
        return $this->_context;
    }
}
