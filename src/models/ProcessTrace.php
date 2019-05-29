<?php

namespace luya\payment\models;

use Yii;
use luya\admin\ngrest\base\NgRestModel;
use luya\helpers\Json;
use luya\admin\aws\DetailViewActiveWindow;

/**
 * Process Trace.
 *
 * File has been created with `crud/create` command.
 *
 * @property integer $id
 * @property integer $process_id
 * @property string $event
 * @property string $message
 * @property integer $timestamp
 * @property text $get
 * @property text $post
 * @property text $server
 * @property string $ip
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class ProcessTrace extends NgRestModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_process_trace';
    }

    /**
     * @inheritdoc
     */
    public static function ngRestApiEndpoint()
    {
        return 'api-payment-processtrace';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        $this->on(self::EVENT_BEFORE_VALIDATE, [$this, 'assignEnvValues']);
    }

    /**
     * Before a payment process trace model is saved, fill the environment data.
     */
    public function assignEnvValues()
    {
        $this->ip = Yii::$app->request->userIP;
        $this->get = (isset($_GET)) ? Json::encode($_GET) : '';
        $this->post = (isset($_POST)) ? Json::encode($_POST) : '';
        $this->server = (isset($_SERVER)) ? Json::encode($_SERVER) : '';
        $this->timestamp = time();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'process_id' => Yii::t('app', 'Process ID'),
            'event' => Yii::t('app', 'Event'),
            'message' => Yii::t('app', 'Message'),
            'timestamp' => Yii::t('app', 'Timestamp'),
            'get' => Yii::t('app', 'Get'),
            'post' => Yii::t('app', 'Post'),
            'server' => Yii::t('app', 'Server'),
            'ip' => Yii::t('app', 'IP'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['process_id', 'timestamp'], 'required'],
            [['process_id', 'timestamp'], 'integer'],
            [['get', 'post', 'server'], 'string'],
            [['ip'], 'string', 'max' => 45],
            [['event', 'message'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestAttributeTypes()
    {
        return [
            'process_id' => 'number',
            'event' => 'text',
            'message' => 'text',
            'ip' => 'text',
            'timestamp' => 'datetime',
            'get' => 'textarea',
            'post' => 'textarea',
            'server' => 'textarea',
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            ['list', ['timestamp', 'event', 'message', 'ip']],
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

    public static function find()
    {
        return parent::find()->orderBy(['timestamp' => SORT_ASC]);
    }
}
