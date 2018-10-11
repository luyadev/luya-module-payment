<p align="center">
  <img src="https://raw.githubusercontent.com/luyadev/luya/master/docs/logo/luya-logo-0.2x.png" alt="LUYA Logo"/>
</p>

# LUYA PAYMENT MODULE

[![LUYA](https://img.shields.io/badge/Powered%20by-LUYA-brightgreen.svg)](https://luya.io)
[![Build Status](https://travis-ci.org/luyadev/luya-module-payment.svg?branch=master)](https://travis-ci.org/luyadev/luya-module-payment)
[![Coverage Status](https://coveralls.io/repos/github/luyadev/luya-module-payment/badge.svg?branch=master)](https://coveralls.io/github/luyadev/luya-module-payment?branch=master)
[![Total Downloads](https://poser.pugx.org/luyadev/luya-module-payment/downloads)](https://packagist.org/packages/luyadev/luya-module-admin)
[![Slack Support](https://img.shields.io/badge/Slack-luyadev-yellowgreen.svg)](https://slack.luya.io/)

This module allows you to integrate payments in a safe and common way. The payment module take care of all the provider required steps (call, create, success, abort, etc.) and provides all the informations for your store.

Currently supported payment providers:

+ [paypal.com](https://paypal.com)
+ [saferpay.com](https://www.saferpay.com)
+ [stripe.com](https://stripe.com)

Create an issue if your payment provider is missing!

## Installation


require the payment module

```sh
composer require luyadev/luya-module-payment:^1.0@dev
```

configure the payment module in your config

```php
'modules' => [
    'payment' => [
        'class' => 'luya\payment\frontend\Module',
        'transaction' => 
            // Paypal Example
            'class' => \luya\payment\transaction\PayPalTransaction::class,
            'clientId' => 'ClientIdFromPayPalApplication',
            'clientSecret' => 'ClientSecretFromPayPalApplication',
            'productDescription' => 'MyOnlineStore Order',
        
            // SaferPay Example
            //'class' => \luya\payment\transaction\SaferPayTransaction::class,
            //'accountId' => 'SAFERPAYACCOUNTID', // each transaction can have specific attributes, saferpay requires an accountId',

            // Stripe
            // 'class' => StripeTransaction::class,
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

Add a transaction to your estore logic, **save the processId** and dispatch() the payment, which will redirect to the payment gatway.

> Make sure to store the `$process->getId()` in your E-Store model in order to retrieve the payment process object to complet/error/abort.

```php
<?php

use luya\payment\Pay;

class StoreCheckoutController extends \luya\web\Controller
{
    public function actionIndex()
    {
        $orderId = 'order-'.uniqid();
        
        // define the pay object
        $process = new Pay();
        $process->setOrderId($orderId);
        $process->setCurrency('EUR');
        $process->setSuccessLink(['success', 'orderId' => $orderId]);
        $process->setErrorLink(['error', 'orderId' => $orderId]);
        $process->setAbortLink(['abort', 'orderId' => $orderId]);
        $process->addItem('Product 1', 1, 200);

        // prepare the order and store the process->getId()
        // ....
        $payId = $process->getId();
        // store this payId in your estore object, where you where also saving the orderId, customer data, customer basket, etc. 

        return $process->dispatch($this);
    }
    
    public function actionSuccess($orderId)
    {
        $id = Pay::close(Yii::$app->session->get('storeTransactionId', 0), Pay::STATE_SUCCESS);
        
        // find the order in your estore logic model
        // $order = new EstoreOrder::findOne(['orderId' => $orderId]); // make sure you have a flag which ensures the state of the order (success = 0)
        // ...
    }
    
    public function actionError($orderId)
    {
        $id = Pay::close(Yii::$app->session->get('storeTransactionId', 0), Pay::STATE_ERROR);

        // find the order in your estore logic model
        // $order = new EstoreOrder::findOne(['orderId' => $orderId]); // make sure you have a flag which ensures the state of the order (success != 1)
        // ...
    }
    
    public function actionAbort($orderId)
    {
        $id = Pay::close(Yii::$app->session->get('storeTransactionId', 0), Pay::STATE_ABORT);

        // find the order in your estore logic model
        // $order = new EstoreOrder::findOne(['orderId' => $orderId]); // make sure you have a flag which ensures the state of the order (success != 1)
        // ...
    }
}
```

> You should **not use session** variabels to make the urls for the success, error and abort links as they can be called by notify urls. Lets assume an user has payed with saferpay but saferpay allows to close the window after the payment succeeded (without going back to the store) the success url with be called by the notify process instead of the users browser. In this case the session environment would have been lost.

## Transaction Configs

Current available transaction/provider configs:

### PayPal Transaction

The [PayPal](https://paypal.com) integration:

```php
'class' => PayPalTransaction::className(),
'clientId' => '<CLIENT_ID>',
'clientSecret' => '<CLIENT_SECRET>',
```


|property   |description
|---        |---
|`mode`    |defines whether the paypal transaction should be in `live` or `sandbox` mode. Default value is `live`.
|`productDescription`|The production description name in the paypal process. This is displayed by PayPal in the *shopping cart* list.


### SaferPay Transaction

The [SaferPay](https://saferpay.com) integration:

```php
'class' => SaferPayTransaction::className(),
'accountId' => '<ACCOUNT-ID>',
```

The test account requireds an optional `spPassword` propertie:

```php
'spPassword' => '<SP-PASSWORD-FROM-DOCS>',
```
