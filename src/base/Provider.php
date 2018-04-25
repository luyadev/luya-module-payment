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
 */
abstract class Provider extends BaseObject implements ProviderInterface
{
    /**
     * Helper method to call callable methods.
     *
     * Call a method of a the current object which is prefix with call and sanitize its variables to match action variables.
     *
     * @param string $method The method to call without the `call` prefix.
     * @param array $vars Options to pass to the method.
     */
    public function call($method, array $vars = [])
    {
        return ObjectHelper::callMethodSanitizeArguments($this, 'call' . Inflector::id2camel($method), $vars);
    }
}
