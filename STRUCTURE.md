# NEW STRUCTURE WITH PROCESS AND INTEGRATOR

```php

class Pay
{
    public function setOrderId();

    public function setCurrency();

    public function setSuccessLink();

    public function setBackLink();

    public function setErrorLink();

    public function setAuthToken();

    public function setRandomKey();

    public function setItems(array $items);

    public static function createId()
    {

    }

    public static findById($id)
    {
        $integreator = \luya\payment\frontend\Module::getInstance()->getIntegrator();
        $model = $integrator->getModel();
    }

    public static closeById($id, $state)
    {
        $integreator = \luya\payment\frontend\Module::getInstance()->getIntegrator();
        $model = $integrator->getModel();
    }
}
```

```php
class HeadlessIntegrator implements PaymentProcessIntegratorInterface
{
    public function createModel(HeadlessIntegratorModel $model);

    public function getModel($token, $key): HeadlessIntegratorModel

    public function updateModel(HeadlessIntegratorModel $model);
}
```

```php
class HeadlessIntegratorModel implements PaymentProcessModelInterface
{
    public function getOrderId();

    public function getCurrency();

    public function getAuthToken();

    public function getRandomKey();

    public function getItems();

    public function getApplicationSuccessLink();

    public function getApplicationErrorLink();

    public function getApplicationAbortLink();

    public function getTransactionGatewayCreateLink();

    public function getTransactionGatewayBackLink();

    public function getTransactionGatewayFailLink();

    public function getTransactionGatewayAbortLink();

    public function getTransactionGatewayNotifyLink();

    public function setOrderId();

    public function setCurrency();

    public function setSuccessLink();

    public function setBackLink();

    public function setErrorLink();

    public function setAuthToken();

    public function setRandomKey();

    public function setItems(array $items);
}