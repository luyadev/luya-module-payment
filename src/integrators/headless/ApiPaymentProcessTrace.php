<?php

namespace luya\payment\integrators\headless;

use luya\headless\ActiveEndpoint;

/**
 * Payment Process Trace API
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
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
