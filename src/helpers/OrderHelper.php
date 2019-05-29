<?php

namespace luya\payment\helpers;

use Yii;

/**
 * Helper class for Orders.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class OrderHelper
{
    /**
     * Generate a unique order id based on the next order Id.
     *
     * Example response
     *
     * ```
     * xjf300005
     * ```
     *
     * Assuming the id key is "5"
     *
     * @param nummeric $id The nummeric id to generate.
     * @return string The generated order id e.g. `xjf300005`.
     */
    public static function generateOrderId($id, $zeroAmount = 5)
    {
        return Yii::$app->security->generateRandomString(4) . str_pad($id, $zeroAmount, '0', STR_PAD_LEFT);
    }
}
