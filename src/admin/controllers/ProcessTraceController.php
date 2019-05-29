<?php

namespace luya\payment\admin\controllers;

/**
 * Process Trace Controller.
 *
 * File has been created with `crud/create` command.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class ProcessTraceController extends \luya\admin\ngrest\base\Controller
{
    /**
     * @var string The path to the model which is the provider for the rules and fields.
     */
    public $modelClass = 'luya\payment\models\ProcessTrace';
}
