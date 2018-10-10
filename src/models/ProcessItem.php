<?php

namespace luya\payment\models;

use Yii;
use luya\admin\ngrest\base\NgRestModel;

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
            [['process_id', 'name'], 'required'],
            [['process_id', 'qty', 'amount'], 'integer'],
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
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            ['list', ['name', 'qty', 'amount']],
        ];
    }
}