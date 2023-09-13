<?php

namespace luya\payment\models;

use luya\admin\ngrest\base\NgRestModel;
use Yii;

/**
 * Process Item.
 *
 * File has been created with `crud/create` command.
 *
 * @property integer $id
 * @property integer $process_id
 * @property string $name
 * @property integer $qty
 * @property integer $amount
 * @property integer $total_amount
 * @property boolean $is_tax
 * @property boolean $is_shipping
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class ProcessItem extends NgRestModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_process_item';
    }

    /**
     * @inheritdoc
     */
    public static function ngRestApiEndpoint()
    {
        return 'api-payment-processitem';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'process_id' => Yii::t('app', 'Process ID'),
            'name' => Yii::t('app', 'Name'),
            'qty' => Yii::t('app', 'Qty'),
            'amount' => Yii::t('app', 'Amount'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['process_id', 'name', 'qty'], 'required'],
            [['process_id', 'qty', 'amount', 'is_shipping', 'is_tax', 'total_amount'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestAttributeTypes()
    {
        return [
            'process_id' => 'number',
            'name' => 'text',
            'qty' => 'number',
            'amount' => 'number',
            'total_amount' => 'number',
            'is_shipping' => 'toggleStatus',
            'is_tax' => 'toggleStatus',
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            ['list', ['name', 'qty', 'amount', 'total_amount', 'is_shipping', 'is_tax']],
        ];
    }
}
