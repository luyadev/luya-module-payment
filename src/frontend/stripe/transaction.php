<?php

use luya\helpers\Html;

$this->beginPage();
?><!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?= Html::encode($this->title); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
  <?= $this->render('@payment/stripe/styles.css'); ?>
  </style>
  <script src="https://js.stripe.com/v3/"></script>
  <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div id="payment-wrapper">
  <div id="payment-inline-wrapper">
    <form action="<?= $url; ?>" method="post" id="payment-form">
        <?= $csrf; ?>
        
        <div class="form-row">
          <label for="card-element">
            Credit or debit card
          </label>
          <div id="card-element">
            <!-- A Stripe Element will be inserted here. -->
          </div>

          <!-- Used to display form errors. -->
          <div id="card-errors" role="alert"></div>
        </div>

      <button class="">Submit Payment</button>
    </form>
    <a href="<?= $abortLink; ?>">Abbrechen und Zurück</a>
  </div>
</div>
<script>
<?= $this->render('@payment/stripe/script.js', ['publishableKey' => $publishableKey]); ?>
</script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage(); ?>