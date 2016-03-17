LUYA PAYMENT IMPLEMENTATION
===========================

**under development**

This module allows you to integrate payments in a safe and common way. The payment module take care of all the provider required steps (call, create, success, abort, etc.) and provides all the informations for your store.

Currently supported payment providers:

+ [paypal.com](http://paypal.com)
+ [saferpay.com](http://saferpay.com)

Create an issue if your payment provider is missing!

Installation
---

require the payment module

```sh
composer require luyadev/luya-module-payment
```

configure the payment module in your config

```php
'modules' => [
    'payment' => [
        'class' => 'payment\Module',
    ],
]
```

execute database command

```sh
./vendor/bin/luya migrate
```


add your transaction where ever you are:


```php

class StoreCheckoutController extends \luya\web\Controller
{
    public function actionIndex()
    {
         // The orderId/basketId should be an unique key for each transaction. based on this key the transacton
         // hash and auth token will be created.
        $orderId = 'Order-' . uniqid();
        
       $process = new payment\PaymentProcess([
           'transactionConfig' => [
           
               // SaferPay Example
               'class' => payment\transaction\SaferPayTransaction::className(),
               'accountId' => 'SAFERPAYACCOUNTID', // each transaction can have specific attributes, saferpay requires an accountId',
               
               // Or PayPal
               // 'class' => payment\transaction\PayPalTransaction::className(),
               // 'clientId' => 'ClientIdFromPayPalApplication',
               // 'clientSecret' => 'ClientSecretFromPayPalApplication',
           ],
           'orderId' => $orderId,
           'amount' => 123123, // in cents
           'currency' => 'USD',
           'successLink' => Url::toRoute(['/mystore/store-checkout/success', 'orderId' => $orderId], true), // user has paid successfull
           'errorLink' => Url::toRoute(['/mystore/store-checkout/error', 'orderId' => $orderId], true), // user got a payment error
           'abortLink' => Url::toRoute(['/mystore/store-checkout/abort', 'orderId' => $orderId], true), // user has pushed the back button
       ]);
       
       // store the id in your estore logic model
       // $order = new EstoreOrder();
       // $order->process_id = $process->getId();
       // $order->order_id = $orderId;
       // $order->update();
        
       return $process->dispatch($this); // where $this is the current controller environment
    }
    
    public function actionSuccess($orderId)
    {
        // find the order in your estore logic model
        // $order = new EstoreOrder::findOne(['orderId' => $orderId]); // make sure you have a flag which ensures the state of the order (success = 0)
        
        $process = PaymentProcess::findById($order->process_id);
        
        // create order for customer ...
        // ...
        
        $process->close(PaymentProcess::STATE_SUCCESS);
    }
    
    public function actionError($orderId)
    {
        // find the order in your estore logic model
        // $order = new EstoreOrder::findOne(['orderId' => $orderId]); // make sure you have a flag which ensures the state of the order (success != 1)
        
        $process = PaymentProcess::findById($order->process_id);
        
        // display error for payment
        
        $process->close(PaymentProcess::STATE_ERROR);
    }
    
    public function actionAbort($orderId)
    {
        // find the order in your estore logic model
        // $order = new EstoreOrder::findOne(['orderId' => $orderId]); // make sure you have a flag which ensures the state of the order (success != 1)
        
        $process = PaymentProcess::findById($order->process_id);
        
        // redirect the user back to where he can choose another payment.
        
        $process->close(PaymentProcess::STATE_ABORT);
    }
}
```

> You should **not use session** variabels to make the urls for the success, error and abort links as they can be called be notify urls. Lets assume an user has payed with saferpay but saferpay allows to close the window after the payment succeeded (without going back to the store) the success url with be called by the notify process instead of the users browser. In this case the session environment would have been lost.

Transaction Configs
---

Current available transaction/provider configs

### PayPal

The [PayPal](https://paypal.com) integration:

```php
'class' => PayPalTransaction::className(),
'clientId' => '<CLIENT_ID>',
'clientSecret' => '<CLIENT_SECRET>',
```

additionaly you can enable `sandboxMode` for paypal transaction

```php
'sandboxMode' => true
```

### SaferPay

The [SaferPay](https://saferpay.com) integration:

```php
'class' => SaferPayTransaction::className(),
'accountId' => '<ACCOUNT-ID>',
```

The test account requireds an optional `spPassword` propertie:

```php
'spPassword' => '<SP-PASSWORD-FROM-DOCS>',
```