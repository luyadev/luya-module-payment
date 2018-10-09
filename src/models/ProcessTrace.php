<?php

namespace luya\payment\models;

use Yii;
use luya\admin\ngrest\base\NgRestModel;

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
            'timestamp' => 'number',
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
            ['list', ['process_id', 'event', 'message', 'timestamp', 'get', 'post', 'server']],
            [['create', 'update'], ['process_id', 'event', 'message', 'timestamp', 'get', 'post', 'server']],
            ['delete', false],
        ];
    }
}