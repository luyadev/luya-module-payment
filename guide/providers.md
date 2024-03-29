# Available Payment Providers

A list of all currently built in payment transaction providers. The transaction provider must be defined in the `transaction` key of the `luya\payment\frontend\Module` config.

## Stripe Transaction

The [Stripe](https://stripe.com) transaction integration config:

```php
'payment' => [
    'class' => 'luya\payment\frontend\Module',
    'transaction' => [
'       class' => 'luya\payment\transactions\StripeTransaction',
        'publishableKey' => 'pk_test_....',
        'secretKey' => 'sk_test_.....',
    ]
]
```

+ `publishableKey`: The publishable key from the strip website (starts with pk_).
+ `secretKey`: The secret key from the strip website (starts with sk_).

**test cards**

+ 4000000000003220 - with 3D secure 2

[See all cards](https://stripe.com/docs/testing#regulatory-cards)

## SaferPay Transaction

The [SaferPay](https://saferpay.com) transaction API integration config:

```php
'payment' => [
    'class' => 'luya\payment\frontend\Module',
        'transaction' => [
        'class' => 'luya\payment\transactions\SaferPayTransaction',
        'terminalId' => '12345678',
        'customerId' => '123456',
        'username' => 'API_XXXXX_XXXXXXX',
        'password' => 'JsonApiPwed..........',
        'mode' => 'sandbox',
    ],
]
```

+ `accountId`: The account id from the saferpay docs.
+ `mode`: The mode `live` or `sandbox` values are available.
+ `spPassword`: When using the test account, the spPassword is required.