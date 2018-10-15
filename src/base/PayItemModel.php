<?php


namespace luya\payment\base;

use yii\base\Model;


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
            [['qty', 'amount'], 'integer'],
            [['is_shipping', 'is_tax'], 'boolean'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    public function getTotalAmount()
    {
        return $this->qty * $this->amount;
    }
}