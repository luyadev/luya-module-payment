<?php

namespace luya\payment\integrators\headless;

use luya\headless\ActiveEndpoint;

/**
 * Payment Process Trace API
 */
class ApiPaymentProcessTrace extends ActiveEndpoint
{
    public $id;
    public $process_id;
    public $event;
    public $message;
    public $timestamp;
    public $get;
    public $post;
    public $server;
    public $ip;

    public function getEndpointName()
    {
        return '{{%api-payment-processtrace}}';
    }
}