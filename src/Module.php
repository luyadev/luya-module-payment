<?php

namespace luya\payment;

/**
 * Payment Module.
 *
 * The payment module class to configure in the modules section of your config.
 *
 * ```php
 * 'modules' => [
 *     // ...
 *     'payment' => 'luya\payment\Module',
 *     // ...
 * ]
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 */
class Module extends \luya\base\Module
{
    public $urlRules = [
        ['pattern' => 'payment-create/<lpToken:\w+>/<lpKey:\w+>', 'route' => 'payment/default/create'],
        ['pattern' => 'payment-back/<lpToken:\w+>/<lpKey:\w+>', 'route' => 'payment/default/back'],
        ['pattern' => 'payment-fail/<lpToken:\w+>/<lpKey:\w+>', 'route' => 'payment/default/fail'],
        ['pattern' => 'payment-abort/<lpToken:\w+>/<lpKey:\w+>', 'route' => 'payment/default/abort'],
        ['pattern' => 'payment-notify/<lpToken:\w+>/<lpKey:\w+>', 'route' => 'payment/default/notify'],
    ];
}
