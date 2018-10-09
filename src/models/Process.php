<?php

namespace luya\payment\models;

use Yii;
use luya\admin\ngrest\base\NgRestModel;

/**
 * Process.
 * 
 * File has been created with `crud/create` command. 
 *
 * @property integer $id
 * @property string $salt
 * @property string $hash
 * @property string $random_key
 * @property integer $amount
 * @property string $currency
 * @property string $order_id
 * @property string $success_link
 * @property string $error_link
 * @property string $abort_link
 * @property integer $close_state
 * @property tinyint $is_closed
 */
class Process extends NgRestModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_process';
    }

    /**
     * @inheritdoc
     */
    public static function ngRestApiEndpoint()
    {
        return 'api-payment-process';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'salt' => Yii::t('app', 'Salt'),
            'hash' => Yii::t('app', 'Hash'),
            'random_key' => Yii::t('app', 'Random Key'),
            'amount' => Yii::t('app', 'Amount'),
            'currency' => Yii::t('app', 'Currency'),
            'order_id' => Yii::t('app', 'Order ID'),
            'success_link' => Yii::t('app', 'Success Link'),
            'error_link' => Yii::t('app', 'Error Link'),
            'abort_link' => Yii::t('app', 'Abort Link'),
            'close_state' => Yii::t('app', 'Close State'),
            'is_closed' => Yii::t('app', 'Is Closed'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['salt', 'hash', 'random_key', 'amount', 'currency', 'order_id', 'success_link', 'error_link', 'abort_link'], 'required'],
            [['amount', 'close_state', 'is_closed'], 'integer'],
            [['salt', 'hash'], 'string', 'max' => 120],
            [['random_key'], 'string', 'max' => 32],
            [['currency'], 'string', 'max' => 10],
            [['order_id'], 'string', 'max' => 50],
            [['success_link', 'error_link', 'abort_link'], 'string', 'max' => 255],
            [['hash'], 'unique'],
            [['random_key'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestAttributeTypes()
    {
        return [
            'salt' => 'text',
            'hash' => 'text',
            'random_key' => 'text',
            'amount' => 'number',
            'currency' => 'text',
            'order_id' => 'text',
            'success_link' => 'text',
            'error_link' => 'text',
            'abort_link' => 'text',
            'close_state' => 'number',
            'is_closed' => 'number',
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            ['list', ['salt', 'hash', 'random_key', 'amount', 'currency', 'order_id', 'success_link', 'error_link', 'abort_link', 'close_state', 'is_closed']],
            [['create', 'update'], ['salt', 'hash', 'random_key', 'amount', 'currency', 'order_id', 'success_link', 'error_link', 'abort_link', 'close_state', 'is_closed']],
            ['delete', false],
        ];
    }
}