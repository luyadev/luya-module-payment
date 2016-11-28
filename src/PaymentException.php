<?php

namespace luya\payment;

/**
 * Payment Exceptions.
 * 
 * @author Basil Suter <basil@nadar.io>
 */
class PaymentException extends \luya\Exception
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'LUYA Payment Provider Exception';
    }
}
