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

    /**
     * @var string Certain transactions allows you to configure a color for the payment page.
     */
    public $color = '#e50060';

    /**
     * @var string Certain transactions allows you to configure a title for the payment. For example `John Doe's Estore`.
     */
    public $title;

    private $_model;
    
    /**
     * Setter method for payment process.
     *
     * @param PayModel $process
     */
    public function setModel(PayModel $model)
    {
        $this->_model = $model;
    }
    
    /**
     * Getter method for model
     *
     * @return PayModel
     */
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

    /**
     * Setter method for Integrator.
     *
     * @param IntegratorInterface $integrator
     */
    public function setIntegrator(IntegratorInterface $integrator)
    {
        $this->_integrator = $integrator;
    }

    /**
     * Getter method for Integrator
     * 
     * @return IntegratorInterface
     */
    public function getIntegrator()
    {
        return $this->_integrator;
    }

    /**
     * Redirect to the transaction `back`.
     * 
     * > Those methods are internal redirects between of actions inside the payment controller and not application urls!
     *
     * @return \yii\web\Response
     */
    public function redirectTransactionBack()
    {
        return $this->getContext()->redirect($this->getModel()->getTransactionGatewayBackLink());
    }

    /**
     * Redirect to the transaction `notify`.
     * 
     * > Those methods are internal redirects between of actions inside the payment controller and not application urls!
     *
     * @return \yii\web\Response
     */
    public function redirectTransactionNotify()
    {
        return $this->getContext()->redirect($this->getModel()->getTransactionGatewayNotifyLink());
    }

    /**
     * Redirect to the transaction `fail`.
     * 
     * > Those methods are internal redirects between of actions inside the payment controller and not application urls!
     *
     * @return \yii\web\Response
     */
    public function redirectTransactionFail()
    {
        return $this->getContext()->redirect($this->getModel()->getTransactionGatewayFailLink());
    }

    /**
     * Redirect to the transaction `abort`.
     * 
     * > Those methods are internal redirects between of actions inside the payment controller and not application urls!
     *
     * @return \yii\web\Response
     */
    public function redirectTransactionAbort()
    {
        return $this->getContext()->redirect($this->getModel()->getTransactionGatewayAbortLink());
    }

    /**
     * Redirect back to the application success action.
     *
     * @return \yii\web\Response
     */
    public function redirectApplicationSuccess()
    {
        $url = $this->getModel()->getApplicationSuccessLink();
        
        $closable = $this->getIntegrator()->closeModel($this->getModel(), Pay::STATE_SUCCESS);

        if (!$closable) {
            throw new PaymentException("Unable to close the model, maybe its already closed.");
        }

        return $this->getContext()->redirect($url);
    }

    /**
     * Redirect back to the application abort action.
     *
     * @return \yii\web\Response
     */
    public function redirectApplicationAbort()
    {
        $url = $this->getModel()->getApplicationAbortLink();
        $closable = $this->getIntegrator()->closeModel($this->getModel(), Pay::STATE_ABORT);

        if (!$closable) {
            throw new PaymentException("Unable to close the model, maybe its already closed.");
        }

        return $this->getContext()->redirect($url);
    }

    /**
     * Redirect back to the application error action.
     *
     * @return \yii\web\Response
     */
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
