# CHANGELOG

All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org/).
In order to read more about upgrading and BC breaks have a look at the [UPGRADE Document](UPGRADE.md).

## 1.1.1 (6. January 2020)

+ Ensure order id contains no special chars.

## 1.1.0 (29. July 2019)

+ [#13](https://github.com/luyadev/luya-module-payment/issues/13) Use new Stripe Payment Intents over Checkout API in order to support SCA checkout flow.
+ Fixed bug with large float numbers when using PayPal Provider.

## 1.0.0 (29. May 2019)

+ Move payment transaction config to config file
+ Changed full class API structure.
+ Added abstraction layers which allows you to use payment with headless library.
+ Added admin module with payment informations.
+ Added status of payment while processing (success, back, failure) instead of the controller implementation.

## 1.0.0-RC4 (19. December 2017)

+ First RC