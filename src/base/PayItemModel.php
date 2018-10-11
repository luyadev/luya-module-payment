<?php


namespace luya\payment\base;

use yii\base\Model;


class PayItemModel extends Model
{
    public $name;
    public $qty;
    public $amount;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['qty', 'amount'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }
}