<?php

namespace luya\payment\base;

interface PaymentProcessInterface
{
    public function getTotalAmount();

    public function getOrderId();

    public function getCurrency();

    public function getItems();

    public function getApplicationSuccessLink();

    public function getApplicationErrorLink();

    public function getApplicationAbortLink();

    public function getTransactionGatewayCreateLink();

    public function getTransactionGatewayBackLink();

    public function getTransactionGatewayFailLink();

    public function getTransactionGatewayAbortLink();

    public function getTransactionGatewayNotifyLink();
}