<?php

namespace luya\payment\models;

use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "payment_process".
 *
 * @property integer $id
 * @property string $salt
 * @property string $hash
 * @property string $random_key
 * @property integer $amount
 * @property string $currency
 * @property string $order_id
 * @property string $provider_name
 * @property string $success_link
 * @property string $error_link
 * @property string $abort_link
 * @property string $transaction_config
 * @property integer $close_state
 * @property integer $is_closed
 * @property string $auth_token The generated token for the encoded and decoed transaction config.
 */
class DataPaymentProcessModel extends \yii\db\ActiveRecord
{
    public $auth_token = null;
    
    public function init()
    {
        parent::init();
        
        $this->on(self::EVENT_AFTER_FIND, [$this, 'decodeTransactionConfig']);
        $this->on(self::EVENT_BEFORE_INSERT, [$this, 'encodeTransactionConfig']);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, 'encodeTransactionConfig']);
    }
    
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
    public function rules()
    {
        return [
            [['salt', 'hash', 'random_key', 'amount', 'currency', 'order_id', 'provider_name', 'success_link', 'error_link', 'abort_link', 'transaction_config'], 'required'],
            [['amount', 'close_state', 'is_closed'], 'integer'],
            [['salt', 'hash'], 'string', 'max' => 120],
            [['random_key'], 'string', 'max' => 32],
            [['currency'], 'string', 'max' => 10],
            [['order_id'], 'safe'],
            [['provider_name'], 'string', 'max' => 50],
            [['success_link', 'error_link', 'abort_link'], 'string', 'max' => 255],
            [['hash'], 'unique'],
            [['random_key'], 'unique'],
        ];
    }
    
    public function decodeTransactionConfig()
    {
        $this->transaction_config = Json::decode($this->transaction_config);
    }
    
    public function encodeTransactionConfig()
    {
        if (is_array($this->transaction_config)) {
            $this->transaction_config = Json::encode($this->transaction_config);
        }
    }

    public function createTokens($inputKey)
    {
        $security = Yii::$app->security;
        
        // random string
        $random = $security->generateRandomString(32);
        
        // generate the auth token based from the random string and the inputKey
        $this->auth_token = $security->generatePasswordHash($random . $inputKey);

        // random salt string
        $this->salt = $security->generateRandomString(32);
        
        // generate a hash to compare the auth token from the salt and auth token
        $this->hash = $security->generatePasswordHash($this->salt . $this->auth_token);
        
        // generate a random key to add for for the transaction itself.
        $this->random_key = md5($security->generaterandomKey());
    }
    
    public function validateAuthToken()
    {
        return Yii::$app->security->validatePassword($this->salt.$this->auth_token, $this->hash);
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'salt' => 'Salt',
            'hash' => 'Hash',
            'random_key' => 'Random Key',
            'amount' => 'Amount',
            'currency' => 'Currency',
            'order_id' => 'Order ID',
            'provider_name' => 'Provider Name',
            'success_link' => 'Success Link',
            'error_link' => 'Error Link',
            'abort_link' => 'Abort Link',
            'transaction_config' => 'Transaction Config',
            'close_state' => 'Close State',
            'is_closed' => 'Is Closed',
        ];
    }
    
    public function addPaymentTraceEvent($eventType, $message = null)
    {
        $model = new DataPaymentProcessTraceModel();
        $model->process_id = $this->id;
        $model->event = $eventType;
        $model->message = $message;
        return $model->save();
    }
}
