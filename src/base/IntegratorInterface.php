<?php

namespace luya\payment\base;

use luya\web\Controller;

interface IntegratorInterface
{
    public function createModel(PayModel $model);

    public function findByKey($key, $token);

    public function findById($id);

    public function closeModel(PayModel $model, $state);

    public function addTrace(PayModel $model, $event, $message = null);
}