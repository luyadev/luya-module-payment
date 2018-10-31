<?php

namespace luya\payment\base;

/**
 * An attachable interface to ensure estore integration.
 * 
 * This is just a recommendation for the payment process inside
 * your application and is not used by the payment process itself.
 * 
 * @since 1.0.0
 */
interface OrderInterface
{
    public function getId();

    public function createOrderId($prefix = null);

    public function createToken();

    public function updatePayId($payId);

    public function getPayId();

    public function isClosed();
}