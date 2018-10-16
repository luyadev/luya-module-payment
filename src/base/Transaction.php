<?php

namespace luya\payment\base;

use luya\payment\PaymentProcess;
use yii\web\Controller;
use yii\base\BaseObject;
use luya\payment\Pay;
use luya\payment\PaymentException;

/**
 * Transaction Abstraction.
 *
 * Each transaction must implement the Transaction Abstraction class.
 *
 * @author Basil Suter <basil@nadar.io>
 */
abstract class Transaction extends BaseObject
{

    /**
     * Creates the transaction and mostly redirects to the provider afterwards
     */
    abstract public function create();
    
    /**
     * Return from create into the back
     */
    abstract public function back();
    
    /**
     * Some providers provide a notify link
     */
    abstract public function notify();
    
    /**
     * An error/failure happend
     */
    abstract public function fail();
    
    /**
     * All providers provide an abort/stop link to back into the onlinestore and choose
     */
    abstract public function abort();

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

    private $_integrator;

    public function setIntegrator(IntegratorInterface $integrator)
    {
        $this->_integrator = $integrator;
    }

    public function getIntegrator()
    {
        return $this->_integrator;
    }

    public function redirectTransactionBack()
    {
        return $this->getContext()->redirect($this->getModel()->getTransactionGatewayBackLink());
    }

    public function redirectTransactionNotify()
    {
        return $this->getContext()->redirect($this->getModel()->getTransactionGatewayNotifyLink());
    }

    public function redirectTransactionFail()
    {
        return $this->getContext()->redirect($this->getModel()->getTransactionGatewayFailLink());
    }

    public function redirectTransactionAbort()
    {
        return $this->getContext()->redirect($this->getModel()->getTransactionGatewayAbortLink());
    }

    public function redirectApplicationSuccess()
    {
        $url = $this->getModel()->getApplicationSuccessLink();
        
        $closable = $this->getIntegrator()->closeModel($this->getModel(), Pay::STATE_SUCCESS);

        if (!$closable) {
            throw new PaymentException("Unable to close the model, maybe its already closed.");
        }

        return $this->getContext()->redirect($url);
    }

    public function redirectApplicationAbort()
    {
        $url = $this->getModel()->getApplicationAbortLink();
        $closable = $this->getIntegrator()->closeModel($this->getModel(), Pay::STATE_ABORT);

        if (!$closable) {
            throw new PaymentException("Unable to close the model, maybe its already closed.");
        }

        return $this->getContext()->redirect($url);
    }

    public function redirectApplicationError()
    {
        $url = $this->getModel()->getApplicationErrorLink();
        $closable = $this->getIntegrator()->closeModel($this->getModel(), Pay::STATE_ERROR);

        if (!$closable) {
            throw new PaymentException("Unable to close the model, maybe its already closed.");
        }

        return $this->getContext()->redirect($url);
    }
}
