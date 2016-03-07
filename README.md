LUYA PAYMENT IMPLEMENTATION
===========================

**under development**

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
           'successLink' => Url::toRoute(['/mystore/store-checkout/success'], true), // user has paid successfull
           'errorLink' => Url::toRoute(['/mystore/store-checkout/error'], true), // user got a payment error
           'abortLink' => Url::toRoute(['/mystore/store-checkout/abort'], true), // user has pushed the back button
       ]);
        
       Yii::$app->session->set('storeTransactionId', $process->getId()); // you can store this information in your shop logic to know the transaction id later on!
        
       return $process->dispatch($this); // where $this is the current controller environment
    }
    
    public function actionSuccess()
    {
        $process = PaymentProcess::findById(Yii::$app->session->get('storeTransactionId', 0));
        
        // create order for customer ...
        // ...
        
        $process->close(PaymentProcess::STATE_SUCCESS);
    }
    
    public function actionError()
    {
        $process = PaymentProcess::findById(Yii::$app->session->get('storeTransactionId', 0));
        
        // display error for payment
        
        $process->close(PaymentProcess::STATE_ERROR);
    }
    
    public function actionAbort()
    {
        $process = PaymentProcess::findById(Yii::$app->session->get('storeTransactionId', 0));
        
        // redirect the user back to where he can choose another payment.
        
        $process->close(PaymentProcess::STATE_ABORT);
    }
}
```

Transaction Configs
===================

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