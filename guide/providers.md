# Available Payment Providers

Current available transaction/provider configs:

## Stripe Transaction

...TBD

## PayPal Transaction

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


## SaferPay Transaction

The [SaferPay](https://saferpay.com) integration:

```php
'class' => SaferPayTransaction::className(),
'accountId' => '<ACCOUNT-ID>',
```

The test account requireds an optional `spPassword` propertie:

```php
'spPassword' => '<SP-PASSWORD-FROM-DOCS>',
```
