<?php

namespace luya\payment\admin\apis;

use luya\admin\components\Auth;
use luya\payment\models\Process;
use yii\web\ForbiddenHttpException;

/**
 * Process Controller.
 *
 * File has been created with `crud/create` command.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class ProcessController extends \luya\admin\ngrest\base\Api
{
    /**
     * @var string The path to the model which is the provider for the rules and fields.
     */
    public $modelClass = 'luya\payment\models\Process';

    public function actionPermissions()
    {
        return [
            'find-by-key' => Auth::CAN_VIEW,
        ];
    }

    public function withRelations()
    {
        return ['items'];
    }

    public function actionFindByKey($key, $token)
    {
        $model = Process::find()->where(['random_key' => $key])->with(['items'])->one();

        if (!$model) {
            return false;
        }

        $model->auth_token = $token;
        if (!$model->validateAuthToken()) {
            throw new ForbiddenHttpException("Invalid auth token and therefore forbidden.");
        }

        return $model;
    }
}
