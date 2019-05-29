<?php

namespace luya\payment;

use Yii;
use luya\helpers\Url;
use luya\web\Controller;
use luya\payment\frontend\Module;
use luya\payment\base\PayModel;
use luya\payment\models\ProcessItem;
use luya\payment\base\PayItemModel;// Rename to PayArticle?
use yii\base\InvalidConfigException;

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

    private $_totalAmount;

    public function setTotalAmount($amount)
    {
        $this->_totalAmount = $amount;
    }

    private $_orderId;

    public function setOrderId($orderId)
    {
        $this->_orderId = $orderId;
    }

    private $_currency;

    /**
     * The 3-letter ISO 4217 currency code.
     *
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        if (strlen($currency) !== 3) {
            throw new InvalidConfigException("The currency must be a 3-letter ISO 4217 code.");
        }
        
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

    /**
     * Add an item.
     *
     * The amount is always the amount for 1 qty! not the total amount
     *
     * @param string $name The name of the product item.
     * @param integer $qty The number of items.
     * @param integer $amount The price in smallest unit for **1 item** not total price.
     */
    public function addItem($name, $qty, $amount)
    {
        $this->internalAddItem($name, $qty, $amount, false, false);
    }

    /**
     * Add shipping product
     *
     * @param string $name The name of the shipping product like "International Shipping"
     * @param string $amount
     */
    public function addShipping($name, $amount)
    {
        $this->internalAddItem($name, 1, $amount, false, true);
    }

    /**
     * A tax item.
     *
     * @param string $name
     * @param integer $amount
     */
    public function addTax($name, $amount)
    {
        $this->internalAddItem($name, 1, $amount, true, false);
    }

    private function internalAddItem($name, $qty, $amount, $isTax, $isShipping)
    {
        $item = new PayItemModel();
        $item->name = $name;
        $item->qty = $qty;
        $item->amount = $amount;
        $item->is_tax = (int) $isTax;
        $item->is_shipping = (int) $isShipping;

        if (!$item->validate(['name', 'qty', 'amount', 'is_tax', 'is_shipping'])) {
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

        if (empty($this->_orderId) || empty($this->_totalAmount) || empty($this->_currency) || is_null($this->_successLink) || is_null($this->_errorLink) || is_null($this->_abortLink)) {
            throw new PaymentException("orderId, totalAmount, currency, successLink, errorLink and abortLink properties can not be null!");
        }
        $amount = 0;
        foreach ($this->_items as $item) {
            $amount += $item->getTotalAmount();
        }

        if ($this->_totalAmount !== $amount) {
            throw new PaymentException("The amount provided trough items,shipping & tax ({$amount}) must be equal the provided totalAmount ({$this->_totalAmount}).");
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

    public static function isSuccess($id)
    {
        $model = self::findById($id);

        if (!$model) {
            return false;
        }

        if ($model->isClosed && $model->closeState == self::STATE_SUCCESS) {
            return true;
        }

        return false;
    }
}
