<?php

namespace luya\payment\base;

use luya\helpers\ObjectHelper;
use yii\base\Object;
use yii\helpers\Inflector;

abstract class Provider extends Object implements ProviderInterface
{
    /**
     * Call a method of a the current object which is prefix with call and sanitize its variables to match action variables.
     *
     * @param string $method
     * @param array $vars
     */
    public function call($method, array $vars = [])
    {
        return ObjectHelper::callMethodSanitizeArguments($this, 'call' . Inflector::id2camel($method), $vars);
    }
}
