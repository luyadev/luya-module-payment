<?php

namespace luya\payment\admin;

class Module extends \luya\admin\base\Module
{
    public $apis = [
        'api-payment-process' => 'luya\payment\admin\apis\ProcessController',
        'api-payment-processtrace' => 'luya\payment\admin\apis\ProcessTraceController',
        'api-payment-processitem' => 'luya\payment\admin\apis\ProcessItemController',

    ];
    
    public function getMenu()
    {
        return (new \luya\admin\components\AdminMenuBuilder($this))
            ->node('Payment', 'payment')
                ->group('Data')
                    ->itemApi('Payment', 'paymentadmin/process/index', 'label', 'api-payment-process')
                    ->itemApi('Article', 'paymentadmin/process-item/index', 'label', 'api-payment-processitem')
                    ->itemApi('Log', 'paymentadmin/process-trace/index', 'label', 'api-payment-processtrace');

    }

}