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
 * @property string $success_link
 * @property string $error_link
 * @property string $abort_link
 * @property integer $close_state
 * @property integer $is_closed
 * @property string $auth_token The generated token for the encoded and decoed transaction config.
 */
class DataPaymentProcessModel extends \yii\db\ActiveRecord
{
    public $auth_token = null;
    
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
            [['salt', 'hash', 'random_key', 'amount', 'currency', 'order_id', 'success_link', 'error_link', 'abort_link'], 'required'],
            [['amount', 'close_state', 'is_closed'], 'integer'],
            [['salt', 'hash'], 'string', 'max' => 120],
            [['random_key'], 'string', 'max' => 32],
            [['currency'], 'string', 'max' => 10],
            [['order_id'], 'safe'],
            [['success_link', 'error_link', 'abort_link'], 'string', 'max' => 255],
            [['hash'], 'unique'],
            [['random_key'], 'unique'],
        ];
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

        // encode the token with base 64 in order to remove conflicting http url signs
        $this->auth_token = base64_encode($this->auth_token);
        
        // generate a random key to add for for the transaction itself.
        $this->random_key = md5($security->generaterandomKey());
    }
    
    public function validateAuthToken()
    {
        $token = base64_decode($this->auth_token);
        return Yii::$app->security->validatePassword($this->salt.$token, $this->hash);
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
            'success_link' => 'Success Link',
            'error_link' => 'Error Link',
            'abort_link' => 'Abort Link',
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
