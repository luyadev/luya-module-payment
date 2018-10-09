<?php

namespace luya\payment\admin\apis;

/**
 * Process Controller.
 * 
 * File has been created with `crud/create` command. 
 */
class ProcessController extends \luya\admin\ngrest\base\Api
{
    /**
     * @var string The path to the model which is the provider for the rules and fields.
     */
    public $modelClass = 'luya\payment\models\Process';
}