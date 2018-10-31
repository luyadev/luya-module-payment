<?php


namespace luya\payment\base;

use yii\base\Model;

/**
 * A payment item.
 * 
 * Describeds are product which is part of the payment, like a basket entry.
 * 
 * @since 1.0.0
 */
class PayItemModel extends Model
{
    public $name;
    public $qty;
    public $amount;
    public $is_shipping;
    public $is_tax;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['qty', 'amount', 'is_shipping', 'is_tax'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    public function getTotalAmount()
    {
        return $this->qty * $this->amount;
    }
}