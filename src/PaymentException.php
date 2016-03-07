<?php

namespace payment;

class PaymentException extends \luya\Exception
{
    public function getName()
    {
        return 'LUYA Payment Provider Exception';
    }
}
