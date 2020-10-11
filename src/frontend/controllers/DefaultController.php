<?php

namespace luya\payment\frontend\controllers;

use luya\payment\base\PayModel;
use yii\filters\HttpCache;

/**
 * Default Payment Controller.
 *
 * This controller handles the internal payment process and transactions.
 *
 * @property \luya\payment\frontend\Module $module
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class DefaultController extends \luya\web\Controller
{
    /**
     * Disable cache
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        $behaviors[] = [
            'class' => HttpCache::class,
            'cacheControlHeader' => 'no-store, no-cache',
            'lastModified' => function ($action, $params) {
                return time();
            },
        ];
        
        return $behaviors;
    }

    /**
     * Undocumented function
     *
     * @param PayModel|boolean $model
     * @return void
     */
    private function ensureModelState($model)
    {
        /** @var PayModel $model */

        // unable to find the model means $model is false.
        if (!$model) {
            return $this->goHome();
        }

        if ($model->isClosedSuccess()) {
            return $this->redirect($model->getApplicationSuccessLink());
        }

        if ($model->isClosedAbort()) {
            return $this->redirect($model->getApplicationAbortLink());
        }

        if ($model->isClosedError()) {
            return $this->redirect($model->getApplicationErrorLink());
        }

        // Its closed, but we can not determine a status.
        if ($model->isClosed()) {
            return $this->redirect($model->getApplicationErrorLink());
        }

        return false;
    }

    /**
     * Create new payment
     *
     * @param string $lpToken The LUYA payment token.
     * @param string $lpKey the LUYA payment key.
     * @return mixed In general the internal methods redirect to urls.
     */
    public function actionCreate($lpToken, $lpKey)
    {
        $integrator = $this->module->getIntegrator();
        $model = $integrator->findByKey($lpKey, $lpToken);
        $integrator->addTrace($model, __METHOD__);

        $state = $this->ensureModelState($model);
        if ($state !== false) {
            return $state;
        }
        
        $this->module->transaction->setIntegrator($integrator);
        $this->module->transaction->setModel($model);
        $this->module->transaction->setContext($this);
        
        
        return $this->module->transaction->create();
    }
    
    /**
     * The action which is opened when coming back from the payment page.
     *
     * @param string $lpToken The LUYA payment token.
     * @param string $lpKey the LUYA payment key.
     * @return mixed In general the internal methods redirect to urls.
     */
    public function actionBack($lpToken, $lpKey)
    {
        $integrator = $this->module->getIntegrator();
        $model = $integrator->findByKey($lpKey, $lpToken);
        $integrator->addTrace($model, __METHOD__);

        $state = $this->ensureModelState($model);
        if ($state !== false) {
            return $state;
        }
        
        $this->module->transaction->setIntegrator($integrator);
        $this->module->transaction->setModel($model);
        $this->module->transaction->setContext($this);
        
        return $this->module->transaction->back();
    }
    
    /**
     * Failed payment response.
     *
     * This can be called by an internal call from the provider after the user (unsuccessfull) finished the payment.
     *
     * @param string $lpToken The LUYA payment token.
     * @param string $lpKey the LUYA payment key.
     * @return mixed In general the internal methods redirect to urls.
     */
    public function actionFail($lpToken, $lpKey)
    {
        $integrator = $this->module->getIntegrator();
        $model = $integrator->findByKey($lpKey, $lpToken);
        $integrator->addTrace($model, __METHOD__);

        $state = $this->ensureModelState($model);
        if ($state !== false) {
            return $state;
        }
        
        $this->module->transaction->setIntegrator($integrator);
        $this->module->transaction->setModel($model);
        $this->module->transaction->setContext($this);
        
        return $this->module->transaction->fail();
    }
    
    /**
     * Abort button pressed by the user.
     *
     * @param string $lpToken The LUYA payment token.
     * @param string $lpKey the LUYA payment key.
     * @return mixed In general the internal methods redirect to urls.
     */
    public function actionAbort($lpToken, $lpKey)
    {
        $integrator = $this->module->getIntegrator();
        $model = $integrator->findByKey($lpKey, $lpToken);
        $integrator->addTrace($model, __METHOD__);
        
        $state = $this->ensureModelState($model);
        if ($state !== false) {
            return $state;
        }

        $this->module->transaction->setIntegrator($integrator);
        $this->module->transaction->setModel($model);
        $this->module->transaction->setContext($this);
        
        return $this->module->transaction->abort();
    }
    
    /**
     * Notification from the Payment Provider.
     *
     * This is commonly a background process.
     *
     * @param string $lpToken The LUYA payment token.
     * @param string $lpKey the LUYA payment key.
     * @return mixed In general the internal methods redirect to urls.
     */
    public function actionNotify($lpToken, $lpKey)
    {
        $integrator = $this->module->getIntegrator();
        $model = $integrator->findByKey($lpKey, $lpToken);
        $integrator->addTrace($model, __METHOD__);
        
        $state = $this->ensureModelState($model);
        if ($state !== false) {
            return $state;
        }

        $this->module->transaction->setIntegrator($integrator);
        $this->module->transaction->setModel($model);
        $this->module->transaction->setContext($this);
        
        return $this->module->transaction->notify();
    }
}
