# Integrators

The integrator allows you the way the payment frontend is talking to the payment admin (which has the models to store data).

> If nothing is defined in payment frontend module configuration, the database integrator is used by default.

## Database Integrator

```php
'payment' => [
    'class' => 'luya\payment\frontend\Module',
    'transaction' => [
        'class' => 'luya\payment\transactions\StripeTransaction',
        'publishableKey' => 'pk_test_',
        'secretKey' => 'sk_test_',
    ],
    'integrator' => [
        'class' => 'luya\payment\integrators\DatabaseIntegrator',
    ]
]
```


## Headless Integrator

Headless Integration with an already existing luya\headless\Client component:

```php
'payment' => [
    'class' => 'luya\payment\frontend\Module',
    'transaction' => [
        'class' => 'luya\payment\transactions\StripeTransaction',
        'publishableKey' => 'pk_test_',
        'secretKey' => 'sk_test_',
    ],
    'integrator' => [
        'class' => 'luya\payment\integrators\HeadlessIntegrator',
        'client' => function () {
            return Yii::$app->api->client;
        }
    ]
]
```

Or if not defined use `accessToken` and `serverUrl`:

```php
'payment' => [
    'class' => 'luya\payment\frontend\Module',
    'transaction' => [
        'class' => 'luya\payment\transactions\StripeTransaction',
        'publishableKey' => 'pk_test_',
        'secretKey' => 'sk_test_',
    ],
    'integrator' => [
        'class' => 'luya\payment\integrators\HeadlessIntegrator',
        'accessToken' => 'my-secret-api-user-token',
        'serverUrl' => 'https://luya-website.com',
    ]
]
```
