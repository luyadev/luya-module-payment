<?php

namespace luya\payment;

use Yii;
use luya\helpers\Url;
use luya\web\Controller;
use luya\payment\frontend\Module;
use luya\payment\base\PayModel;
use luya\payment\models\ProcessItem;
use luya\payment\base\PayItemModel; // Rename to PayArticle?

/**
 * Create new Payment.
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Pay
{
    const STATE_PENDING = 0;

    const STATE_SUCCESS = 1;
    
    const STATE_ERROR = 2;
    
    const STATE_ABORT = 3;

    private $_orderId;

    public function setOrderId($orderId)
    {
        $this->_orderId = $orderId;
    }

    private $_currency;

    public function setCurrency($currency)
    {
        $this->_currency = $currency;
    }

    private $_successLink;

    public function setSuccessLink($link)
    {
        $this->_successLink = is_array($link) ? Url::toRoute($link, true) : $link;
    }

    private $_abortLink;

    public function setAbortLink($link)
    {
        $this->_abortLink = is_array($link) ? Url::toRoute($link, true) : $link;
    }

    private $_errorLink;

    public function setErrorLink($link)
    {
        $this->_errorLink = is_array($link) ? Url::toRoute($link, true) : $link;
    }

    private $_items;

    public function addItem($name, $qty, $amount)
    {
        $item = new PayItemModel();
        $item->name = $name;
        $item->qty = $qty;
        $item->amount = $amount;

        if (!$item->validate(['name', 'qty', 'amount'])) {
            throw new PaymentException("Unable to validate the item model. Validation failed: " . var_export($item->getErrors(), true));
        }

        $this->_items[] = $item;
    }

    private $_model;

    protected function getCreateModel()
    {
        if ($this->_model) {
            return $this->_model;
        }

        if (empty($this->_orderId) || empty($this->_currency) || is_null($this->_successLink) || is_null($this->_errorLink) || is_null($this->_abortLink)) {
            throw new PaymentException("orderId, currency, successLink, errorLink and abortLink properties can not be null!");
        }
        $amount = 0;
        foreach ($this->_items as $item) {
            $amount += $item->amount;
        }

        $model = new PayModel();
        $model->orderId = $this->_orderId;
        $model->currency = $this->_currency;
        $model->successLink = $this->_successLink;
        $model->abortLink = $this->_abortLink;
        $model->errorLink = $this->_errorLink;
        $model->items = $this->_items;
        $model->totalAmount = $amount;

        if (!$model->validate()) {
            throw new PaymentException("unable to validate the pay model.");
        }

        $integrator = Module::getInstance()->getIntegrator();
        if ($integrator->createModel($model)) {
            Yii::warning("model created with id: " . $model->getId());
            return $this->_model = $model;
        }

        throw new PaymentException("Error while creating the pay model by the integratort.");
    }

    /**
     * Get the current payment pay model id. 
     * 
     * You can store this information in the estore logic of your project.
     *
     * @return integer The id from the pay process.
     */
    public function getId()
    {
        return $this->getCreateModel()->getId();
    }

    public function getRandomKey()
    {
        return $this->getCreateModel()->getRandomKey();
    }

    public function getAuthToken()
    {
        return $this->getCreateModel()->getAuthToken();
    }
    
    /**
     * Dispatch the current controller to the getTransactionGatewayCreat link.
     *
     * @param \luya\web\Controller $controller The context controller object.
     * @throws Exception
     */
    public function dispatch(Controller $controller)
    {
        $url = $this->getCreateModel()->getTransactionGatewayCreateLink();
        
        return $controller->redirect($url);
    }

    /**
     * Find the model by an id.
     *
     * @param integer $id
     * @return PayModel
     */
    public static function findById($id)
    {
        $integrator = Module::getInstance()->getIntegrator();
        $model = $integrator->findById($id);

        return $model;
    }

    /**
     * Close the current payment for a given id with a state message (success, error, abort).
     *
     * @param integer $id
     * @param integer $state
     * @return PayModel
     */
    public static function close($id, $state)
    {
        $model = self::findById($id);

        if (!$model) {
            return false;
        }

        $integrator = Module::getInstance()->getIntegrator();
        if ($integrator->closeModel($model, $state)) {
            return $model;
        }

        return false;
    }
}