# CHANGELOG

All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org/).
In order to read more about upgrading and BC breaks have a look at the [UPGRADE Document](UPGRADE.md).

## 4.0.1 (31. Oktober 2023)

+ [#30](https://github.com/luyadev/luya-module-payment/pull/30) Added Bahasa translation

## 4.0.0 (13. Septmeber 2023)

+ [#28](https://github.com/luyadev/luya-module-payment/pull/28) Removed PayPal support from built in providers due to not upgraded composer SDK.
+ [#28](https://github.com/luyadev/luya-module-payment/pull/28) Removed SaferPayLegacy Provider
+ [#28](https://github.com/luyadev/luya-module-payment/pull/28) Dropped support for PHP 7.x versions

## 3.0.4 (8. February 2022)

+ Upgrade stripe php sdk to allow php 8 compatibility

## 3.0.3 (27. July 2021)

+ Allow composer constraint for version 2.0 of LUYA core.

## 3.0.2 (27. May 2021)

+ [#26](https://github.com/luyadev/luya-module-payment/pull/26) Ensure the CSRF meta informations are registered, otherwise a javascript error is thrown.

## 3.0.1 (10. February 2021)

+ [#25](https://github.com/luyadev/luya-module-payment/pull/25) Fix issue where already captured safer pay process throws an `402 Action Failed` exception.

## 3.0 (5. January 2021)

> This release contains significant changes regarding SaferPay integration. Check the [UPGRADE document](UPGRADE.md) to read more about breaking changes.

+ [#22](https://github.com/luyadev/luya-module-payment/pull/22) Remove SaferPay HTTPS Interface provider as its deprecated until end of 2020.

## 2.1 (11. October 2020)

+ [#21](https://github.com/luyadev/luya-module-payment/pull/21) New methods to close a payment process within a transaction.
+ [#20](https://github.com/luyadev/luya-module-payment/pull/20) Catch SaferPay interface error message.

## 2.0 (13. April 2020)

> This release contains new migrations and requires to run the migrate command after updating. Check the [UPGRADE document](UPGRADE.md) to read more about breaking changes.

+ [#18](https://github.com/luyadev/luya-module-payment/pull/18) New migration for saving payment provider specific informations like IDs.

## 1.1.3 (28. February 2020)

+ Changed german translation
+ Changed version constraint for LUYA Admin Module.

## 1.1.2 (14. February 2020)

+ [#16](https://github.com/luyadev/luya-module-payment/pull/16) New IT translation messages.

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
