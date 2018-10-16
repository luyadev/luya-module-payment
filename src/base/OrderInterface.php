<?php

namespace luya\payment\base;

/**
 * An interface you can attach to your estore order model.
 * 
 * This is just a recommendation for the payment process.
 */
interface OrderInterface
{
    public function getId();

    public function createOrderId($prefix = null);

    public function createToken();

    public function updatePayId($payId);

    public function getPayId();

    public function isDone();
}