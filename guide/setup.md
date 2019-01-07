# How to use

require the payment module

```sh
composer require luyadev/luya-module-payment:^1.0@dev
```

configure the payment module in your config

```php
'modules' => [
    'paymentadmin' => 'luya\payment\admin\Module',
    'payment' => [
        'class' => 'luya\payment\frontend\Module',
        'transaction' => [
            // Paypal Example
            'class' => 'luya\payment\transaction\PayPalTransaction',
            'clientId' => 'ClientIdFromPayPalApplication',
            'clientSecret' => 'ClientSecretFromPayPalApplication',
            'productDescription' => 'MyOnlineStore Order',
        
            // SaferPay Example
            //'class' => 'luya\payment\transaction\SaferPayTransaction',
            //'accountId' => 'SAFERPAYACCOUNTID', // each transaction can have specific attributes, saferpay requires an accountId',

            // Stripe
            // 'class' => 'luya\payment\transaction\StripeTransaction',
            // 'publishableKey' => 'pk_test_....',
            // 'secretKey' => 'sk_test_.....',
        ],
    ],
]
```

execute database command

```sh
./vendor/bin/luya migrate
```

Add a transaction to your estore logic, **save the pay id** and dispatch() the payment, which will redirect to the payment gatway.

> Make sure to store the `$pay->getId()` in your E-Store model in order to retrieve the payment process object to complet/error/abort.

```php
<?php

use luya\payment\Pay;

class StoreCheckoutController extends \luya\web\Controller
{
    public function actionIndex()
    {
        $orderId = 'order-'.uniqid();
        
        // define the pay object
        $pay = new Pay();
        $pay->setOrderId($orderId);
        $pay->setCurrency('EUR');
        $pay->setSuccessLink(['success', 'orderId' => $orderId]);
        $pay->setErrorLink(['error']);
        $pay->setAbortLink(['abort']);

        $pay->addItem('Product A', 2, 200); // buying Product A for 2x each 200 cents which is a total amount of 400 cents (the charged value).
        $pay->addTax('VAT 8%', 16);
        $pay->totalAmount(416);

        // prepare the order and store the process->getId()
        // ....
        $payId = $pay->getId();
        // store this payId in your estore object, where you where also saving the orderId, customer data, customer basket, etc. 

        return $pay->dispatch($this);
    }
    
    public function actionSuccess($orderId)
    {
        // find the $payId from the order model.
        // this ensures if someone could open this url directly whether payment process for the given id was sucessfull or not.
        if (!Pay::isSuccess($payId)) {
            throw new \Exception("The request url is invalid, the payment process was not closed successfull.");
        }
    }
    
    public function actionAbort($orderId)
    {
        // redirect to the view where the users klicks "pay" ...
    }

    public function actionError($orderId)
    {
        // display a an error message for the user
    }
}
```

> You should **not use session** variables to make the urls for the success, error and abort links as they can be called by notify urls. Lets assume an user has payed with saferpay but saferpay allows you to close the window after the payment succeeded (without going back to the store). The success url would be called by the notify process of SaferPay instead of the users Browser. In this case the session environment would have been lost and the payment informations page which is triggered would return an exception/error.