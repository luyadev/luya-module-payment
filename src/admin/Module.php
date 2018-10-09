<?php

namespace luya\payment\admin;

class Module extends \luya\admin\base\Module
{
    public $apis = [
        'api-payment-process' => 'luya\payment\admin\apis\ProcessController',
        'api-payment-processtrace' => 'luya\payment\admin\apis\ProcessTraceController',

    ];
    
    public function getMenu()
    {
        return (new \luya\admin\components\AdminMenuBuilder($this))
            ->node('Payment', 'money')
                ->group('Group')
                    ->itemApi('Process', 'paymentadmin/process/index', 'label', 'api-payment-process')
                    ->itemApi('ProcessTrace', 'paymentadmin/process-trace/index', 'label', 'api-payment-processtrace');

    }

}