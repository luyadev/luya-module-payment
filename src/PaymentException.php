<?php

namespace luya\payment;

/**
 * Payment Exceptions.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class PaymentException extends \luya\Exception
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'LUYA Payment Exception';
    }
}
