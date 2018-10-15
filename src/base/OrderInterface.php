<?php

namespace luya\payment\base;

interface OrderInterface
{
    public function getId();

    public function createOrderId($prefix = null);

    public function createToken();

    public function updatePayId($payId);
}