<?php

namespace luya\payment\base;

use luya\helpers\ObjectHelper;
use yii\helpers\Inflector;
use yii\base\BaseObject;

/**
 * Payment Provider Abstraction.
 *
 * Concrect implementation for all Providers.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
abstract class Provider extends BaseObject implements ProviderInterface
{
    /**
     * Helper method to call callable methods.
     *
     * Call a method of a the current object which is prefix with call and sanitize its variables to match action variables.
     *
     * Is currently used as its more readable for integrations as most methos have a lot of parameters.
     * 
     * ```php
     * $provider->call('foo-bar', ['name' => 'john']);
     * ```
     * 
     * would call
     * 
     * ```php
     * public function callFooBar($name)
     * {
     *    return $name;
     * }
     * ```
     *
     * @param string $method The method to call without the `call` prefix.
     * @param array $vars Options to pass to the method.
     */
    public function call($method, array $vars = [])
    {
        return ObjectHelper::callMethodSanitizeArguments($this, 'call' . Inflector::id2camel($method), $vars);
    }
}
