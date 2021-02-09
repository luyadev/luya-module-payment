<?php

namespace luya\payment\base;

use Curl\Curl;
use yii\web\Controller;
use yii\base\BaseObject;
use luya\payment\Pay;
use luya\payment\PaymentException;

/**
 * Transaction Abstraction.
 *
 * Each transaction must implement the Transaction Abstraction class.
 *
 * @property PayModel $model
 * @property Controller $context
 * @property IntegratorInterface $integrator
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
abstract class Transaction extends BaseObject
{
    /**
     * Creates the transaction and mostly redirects to the provider afterwards.
     */
    abstract public function create();
    
    /**
     * The method which is triggered when coming "back from the provider". In generall this is also success.
     *
     * Commonly call `$this->redirectApplicationSuccess()` now.
     */
    abstract public function back();
    
    /**
     * Some providers provide a notify link. The notify method will be called from the payment provider in the
     * background. In generall we want to ensure the payment is closed here.
     *
     * close the paymentsuccessful `$this->closePaymentAsSuccessful()` and try to call the webstore url (maybe
     * this triggers an email) `$this-curlApplicationLink($this->getModel()->getApplicationSuccessLink())`.
     */
    abstract public function notify();
    
    /**
     * Redirect back to the application failure/error page
     *
     * `$this->redirectTransactionFail()`
     */
    abstract public function fail();
    
    /**
     * All providers provide an abort/stop link to back into the onlinestore and choose
     *
     * `$this-redirectTransactionAbort()`
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

    /**
     * @var string The error message which is thrown an logged when an eror happens while closing the model.
     * @since 2.1
     */
    public $errorCloseSuccess = "Unable to close the model as successful, maybe its already closed.";

    /**
     * @var string The error message which is thrown an logged when an eror happens while closing the model.
     * @since 2.1
     */
    public $errorCloseAbort = "Unable to close the model as aborted, maybe its already closed.";

    /**
     * @var string The error message which is thrown an logged when an eror happens while closing the model.
     * @since 2.1
     */
    public $errorCloseError = "Unable to close the model as errored, maybe its already closed.";

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
        
        $this->closePaymentAsSuccessful();

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
        $this->closePaymentAsAborted();

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
        $this->closePaymentAsErrored();

        return $this->getContext()->redirect($url);
    }

    /**
     * Close the current payment model as successful
     *
     * @since 2.1
     */
    protected function closePaymentAsSuccessful()
    {
        $closable = $this->getIntegrator()->closeModel($this->getModel(), Pay::STATE_SUCCESS);

        if (!$closable) {
            $this->getIntegrator()->addTrace($this->getModel(), __METHOD__, $this->errorCloseSuccess);
            throw new PaymentException($this->errorCloseSuccess);
        }
    }

    /**
     * Close the current payment model as aborted
     *
     * @since 2.1
     */
    protected function closePaymentAsAborted()
    {
        $closable = $this->getIntegrator()->closeModel($this->getModel(), Pay::STATE_ABORT);

        if (!$closable) {
            $this->getIntegrator()->addTrace($this->getModel(), __METHOD__, $this->errorCloseAbort);
            throw new PaymentException($this->errorCloseAbort);
        }
    }

    /**
     * Close the current payment model as errored
     *
     * @since 2.1
     */
    protected function closePaymentAsErrored()
    {
        $closable = $this->getIntegrator()->closeModel($this->getModel(), Pay::STATE_ERROR);

        if (!$closable) {
            $this->getIntegrator()->addTrace($this->getModel(), __METHOD__, $this->errorCloseError);
            throw new PaymentException($this->errorCloseError);
        }
    }

    /**
     * CURL a given url in order to ensure estore "success" page is called.
     *
     * @param string $link
     * @return boolean Whether success status code is returned or not.
     * @since 2.1
     */
    protected function curlApplicationLink($link)
    {
        $curl = new Curl();
        $curl->get($link);

        $this->getIntegrator()->addTrace($this->getModel(), __METHOD__, $link . ' | Response Status Code: ' . $curl->getHttpStatus());

        if ($curl->curl_error) {
            $this->getIntegrator()->addTrace($this->getModel(), __METHOD__, $link . ' | CURL Response: ' . $curl->error_message . ' | ' . $curl->response);
        }

        $success = $curl->isSuccess();

        if (!$success) {
            $this->getIntegrator()->addTrace($this->getModel(), __METHOD__, $link . ' | Request Response: ' . $curl->error_message);
        }

        return $success;
    }
}
