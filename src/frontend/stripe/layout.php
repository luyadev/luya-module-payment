<?php
use yii\helpers\Html;

/* @var $this luya\web\View */
/* @var $content string */
?>
<?php $this->beginPage() ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= Html::encode($this->title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->head() ?>
</head>
<body class="payment-standalone">
<?php $this->beginBody(); ?>
    <?= $content ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
