<p align="center">
  <img src="https://raw.githubusercontent.com/luyadev/luya/master/docs/logo/luya-logo-0.2x.png" alt="LUYA Logo"/>
</p>

# LUYA PAYMENT MODULE

[![LUYA](https://img.shields.io/badge/Powered%20by-LUYA-brightgreen.svg)](https://luya.io)
![Tests](https://github.com/luyadev/luya-module-payment/workflows/Tests/badge.svg)
[![Test Coverage](https://api.codeclimate.com/v1/badges/713bfdbebb5a8bce7155/test_coverage)](https://codeclimate.com/github/luyadev/luya-module-payment/test_coverage)
[![Total Downloads](https://poser.pugx.org/luyadev/luya-module-payment/downloads)](https://packagist.org/packages/luyadev/luya-module-payment)

The LUYA Payment module is a very easy way to add payment systems to your Website. The payment providers can be changed trough configuration, without changing code in your application. No more copy paste of Payment code between projects as the LUYA Payment module unifies does tasks and simplifies the integration. The integrated admin modules provides tracking and debuging of payments.

Its even possible to define an `integrator` which allows you to add the module connected to the database (default) or as headless integration connecting to the admin APIs.

Currently supported payment providers:

+ [saferpay.com](https://www.saferpay.com)
+ [stripe.com](https://stripe.com)

![LUYA Payment Stripe](https://raw.githubusercontent.com/luyadev/luya-module-payment/master/stripe.jpeg)

> An example integration for Stripe Payment Provider, for other payment providers the users is redirect to the corresponding Payment Page.

## Documentation

See the [full Documentation](guide/README.md) in order to learn how to integrate the process, use the different providers or change the integrator class.

+ [Documentation](guide/README.md)
