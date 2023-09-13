# Upgrade

## 3.x to 4.0

+ Removed the `SaferPayLegacy` provider, no replacment.
+ Removed `PayPal` provider, no replacment.
+ Dropped support for PHP 7.x versions

## 2.x to 3.0

+ [#22](https://github.com/luyadev/luya-module-payment/pull/22) The SaferPay Transaction has been replaced with the new JSON API and therefore the old HTTPS interface SaferPay Transaction class has been renamed to `SaferPayLegacyTransaction` while the new `SaferPayTransaction` contains the new code to work with the JSON API. Therefore the class parameters which are required has been changed. The new configuration looks as followed:

```php
'payment' => [
    'class' => 'luya\payment\frontend\Module',
    'transaction' => [
        'class' => 'luya\payment\transactions\SaferPayTransaction',
        'terminalId' => '12345678',
        'customerId' => '123456',
        'username' => 'API_XXXXX_XXXXXXX',
        'password' => 'JsonApiPwed..........',
        'mode' => 'prod',
    ]
]
```

## 1.x to 2.0

+ Run the migrate command, as new migrations are available and required!

## RC4 to 1.0

+ The transaction config is not stored in the payment process anymore, it must be registered in the module instead `'transaction' => []`.
+ Run the migrate command `./luya migrate` and import command afterwards `./luya import`.
+ Change the payment module class in the config from `luya\payment\Module` to `luya\payment\frontend\Module`. Aso register the admin module `luya\payment\admin\Module` as `paymentadmin`.
+ Renamed `luya\payment\PaymentProcess` to `luya\payment\Pay`.
+ Register the amount trough `addItem()`, `addShipping()` and `addTax()` in order to ensure the totalAmount must be set as well.
```
$pay = new Pay();
$pay->addItem('Product 1', 1, $amount);
$pay->setOrderId($id);
$pay->setCurrency('CHF');
$pay->setTotalAmount($amount);
$pay->setSuccessLink(['/store/default/success', 'order' => $id]);
$pay->setErrorLink(['/store/default/error',  'order' => $id]);
$pay->setAbortLink(['/store/default/confirm']);
```
+ Included admin module as migrations are stored in the admin `['paymentadmin' => 'luya\payment\admin\Module']`.
