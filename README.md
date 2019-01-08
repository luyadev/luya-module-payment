<p align="center">
  <img src="https://raw.githubusercontent.com/luyadev/luya/master/docs/logo/luya-logo-0.2x.png" alt="LUYA Logo"/>
</p>

# LUYA PAYMENT MODULE

[![LUYA](https://img.shields.io/badge/Powered%20by-LUYA-brightgreen.svg)](https://luya.io)
[![Build Status](https://travis-ci.org/luyadev/luya-module-payment.svg?branch=master)](https://travis-ci.org/luyadev/luya-module-payment)
[![Coverage Status](https://coveralls.io/repos/github/luyadev/luya-module-payment/badge.svg?branch=master)](https://coveralls.io/github/luyadev/luya-module-payment?branch=master)
[![Total Downloads](https://poser.pugx.org/luyadev/luya-module-payment/downloads)](https://packagist.org/packages/luyadev/luya-module-payment)
[![Slack Support](https://img.shields.io/badge/Slack-luyadev-yellowgreen.svg)](https://slack.luya.io/)

The LUYA Payment module is a very easy way to add payment systems to your Website. The payment providers can be changed trough configuration, without changing code in your application. No more copy paste of Payment code between projects as the LUYA Payment module unifies does tasks and simplifies the integration. The integrated admin modules provides tracking and debuging of payments.

Its even possible to define an `integrator` which allows you to add the module connected to the database (default) or as headless integration connecting to the admin APIs.

Currently supported payment providers:

+ [paypal.com](https://paypal.com)
+ [saferpay.com](https://www.saferpay.com)
+ [stripe.com](https://stripe.com)

## Documentation

See the [full Documentation](guide/README.md) in order to learn how to integrate the process, use the different providers or change the integrator class.

+ [Documentation](guide/README.md)