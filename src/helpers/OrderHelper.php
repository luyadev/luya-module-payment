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
     * @param integer $id The nummeric id to generate.
     * @param integer $zeroAmount The amount of zeros to prefix
     * @param integer $randomString The length of the random strings
     * @param string $prefix A prefix which should be added in front of the order number {@since 3.0}
     * @return string The generated order id e.g. `xjf300005`.
     */
    public static function generateOrderId($id, $zeroAmount = 5, $randomString = 4, $prefix = null)
    {
        $random = $randomString > 0  ? strtoupper(Yii::$app->security->generateRandomString($randomString)) : null;
        $string = $random . str_pad($id, $zeroAmount, '0', STR_PAD_LEFT);

        return $prefix . str_replace(['-', '_'], rand(0, 9), $string);
    }
}
