<?php

namespace luya\payment\models;

use Yii;
use luya\admin\ngrest\base\NgRestModel;
use luya\admin\aws\DetailViewActiveWindow;
use luya\payment\PaymentProcess;
use luya\payment\PaymentException;

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
 * @property integer $create_timestamp
 * @property integer $close_timestamp
 * @property integer $state_create
 * @property integer $state_back
 * @property integer $state_fail
 * @property integer $state_abort
 * @property integer $state_notify
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Process extends NgRestModel
{
    const STATE_PENDING = 0;

    const STATE_SUCCESS = 1;

    const STATE_ERROR = 2;

    const STATE_ABORT = 3;

    public $auth_token;

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
    public function init()
    {
        // As the process is mainly used in frontend and can be used without admin module we detach the log behavior.
        $this->detachBehavior('LogBehavior');

        parent::init();

        $this->on(self::EVENT_BEFORE_VALIDATE, function ($event) {

            // ensure order_id is a string value even  when its a number ohter whise validation would fail.
            $this->order_id = (string) $this->order_id;

            if ($this->isNewRecord) {
                $this->create_timestamp = time();
                $this->createTokens();
            }
        });

        $this->on(self::EVENT_AFTER_INSERT, [$this, 'saveItems']);
    }

    private $_items;

    public function setItems(array $items)
    {
        $this->_items = $items;
    }

    public function saveItems()
    {
        foreach ($this->_items as $item) {
            $itemModel = new ProcessItem();
            $itemModel->process_id = $this->id;
            $itemModel->qty = $item['qty'];
            $itemModel->amount = $item['amount'];
            $itemModel->name = $item['name'];
            $itemModel->total_amount = $item['total_amount'];
            $itemModel->is_shipping = $item['is_shipping'];
            $itemModel->is_tax = $item['is_tax'];
            if (!$itemModel->save()) {
                throw new PaymentException("Unable to store the process item due to validation errors.");
            }
        }
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
            'create_timestamp' => Yii::t('app', 'Created at'),
            'close_timestamp' => Yii::t('app', 'Closed Timestamp'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['salt', 'hash', 'random_key', 'amount', 'currency', 'order_id', 'success_link', 'error_link', 'abort_link'], 'required'],
            [['amount', 'close_state', 'is_closed', 'create_timestamp', 'close_timestamp', 'state_notify', 'state_abort', 'state_fail', 'state_back', 'state_create'], 'integer'],
            [['salt', 'hash'], 'string', 'max' => 120],
            [['random_key'], 'string', 'max' => 32],
            [['currency'], 'string', 'max' => 10],
            [['order_id'], 'string', 'max' => 50],
            [['success_link', 'error_link', 'abort_link'], 'string', 'max' => 255],
            [['hash'], 'unique'],
            [['random_key'], 'unique'],
            [['items'], 'safe'],
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
            'close_state' => ['selectArray', 'emptyListValue' => false, 'data' => [self::STATE_PENDING => 'Pending', self::STATE_SUCCESS => 'Success', self::STATE_ABORT => 'Aborted', self::STATE_ERROR => 'Error']],
            'is_closed' => ['toggleStatus', 'interactive' => false],
            'create_timestamp' => 'datetime',
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['auth_token'] = 'auth_token';
        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            ['list', ['order_id', 'create_timestamp', 'amount', 'currency', 'close_state', 'is_closed']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestActiveWindows()
    {
        return [
            ['class' => DetailViewActiveWindow::class],
        ];
    }

    public function ngRestRelations()
    {
        return [
            ['label' => 'Articles', 'targetModel' => ProcessItem::class,'apiEndpoint' => ProcessItem::ngRestApiEndpoint(), 'dataProvider' => $this->getItems()],
            ['label' => 'Log', 'targetModel' => ProcessTrace::class,'apiEndpoint' => ProcessTrace::ngRestApiEndpoint(), 'dataProvider' => $this->getTraces()],
        ];
    }

    /**
     * Get related items
     *
     * @return void
     */
    public function getItems()
    {
        return $this->hasMany(ProcessItem::class, ['process_id' => 'id']);
    }

    /**
     * Get related trace events
     *
     * @return void
     */
    public function getTraces()
    {
        return $this->hasMany(ProcessTrace::class, ['process_id' => 'id']);
    }

    /**
     * Create variables based on the input key.
     *
     * 1. Generate a random string
     * 2. generate a password hash based on random string and input key stored in $auth_token
     * 3. Generate a salt random string
     * 4. generate a password hash from salt and auth token
     * 5. Base 64 encode the auth token
     * 6. Generate randon key and md5
     *
     * Restore and ensure in application:
     *
     * 1. Get the model from with the random key
     * 2. Validate the auth token against this model
     *  a. decode the auth token
     *  b. validate the auth token against the hash from the model.
     *
     * Creates and assignes values to:
     *
     * + auth_token
     * + salt
     * + hash
     * + random_key
     *
     * @param [type] $inputKey
     * @return void
     */
    public function createTokens()
    {
        $inputKey = $this->order_id;

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
    
    /**
     * Validate the auth token against model hash
     *
     * @return void
     */
    public function validateAuthToken()
    {
        $token = base64_decode($this->auth_token);
        return Yii::$app->security->validatePassword($this->salt.$token, $this->hash);
    }

    /**
     * Payment trace short hand.
     *
     * @param string $eventType
     * @param string $message
     * @return boolean Whether saving was successfull or not.
     */
    public function addPaymentTraceEvent($eventType, $message = null)
    {
        $model = new ProcessTrace();
        $model->process_id = $this->id;
        $model->event = $eventType;
        $model->message = $message;
        return $model->save();
    }
}
