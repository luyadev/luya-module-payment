<?php

use luya\helpers\Html;
use luya\web\View;
use luya\payment\frontend\Module;

$this->title = $title;
$icons = [
    'close' => '<svg class="payment-header-close" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="m256 8c-137 0-248 111-248 248s111 248 248 248 248-111 248-248-111-248-248-248zm0 448c-110.5 0-200-89.5-200-200s89.5-200 200-200 200 89.5 200 200-89.5 200-200 200zm101.8-262.2-62.2 62.2 62.2 62.2c4.7 4.7 4.7 12.3 0 17l-22.6 22.6c-4.7 4.7-12.3 4.7-17 0l-62.2-62.2-62.2 62.2c-4.7 4.7-12.3 4.7-17 0l-22.6-22.6c-4.7-4.7-4.7-12.3 0-17l62.2-62.2-62.2-62.2c-4.7-4.7-4.7-12.3 0-17l22.6-22.6c4.7-4.7 12.3-4.7 17 0l62.2 62.2 62.2-62.2c4.7-4.7 12.3-4.7 17 0l22.6 22.6c4.7 4.7 4.7 12.3 0 17z"/></svg>',
    'check' => '<svg class="payment-icon payment-icon-check" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="m173.898 439.404-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0l112.095 112.094 240.095-240.094c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z"/></svg>',
    'back' => '<svg class="payment-icon payment-icon-back" viewBox="0 0 256 512" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="m31.7 239 136-136c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9l-96.3 96.5 96.4 96.4c9.4 9.4 9.4 24.6 0 33.9l-22.6 22.7c-9.4 9.4-24.6 9.4-33.9 0l-136-136c-9.5-9.4-9.5-24.6-.1-34z"/></svg>'
];

$this->registerJsVar('confirmUrl', $url);

$this->registerCss($this->render('@payment/stripe/dist/stripe.css'));
$this->registerJsFile('https://js.stripe.com/v3/');
$this->registerJs($this->render('@payment/stripe/dist/stripe.js', ['publishableKey' => $publishableKey]), View::POS_END);

?>
<div class="payment-wrapper" id="payment-wrapper">
    <div class="payment-wrapper-inner" id="payment-wrapper-inline">
        <div class="payment-header" style="background-color: <?= $color ?>;">
            <p class="payment-header-text payment-text"><?= Html::encode($title, false); ?></p>
            <a class="payment-header-close-link" href="<?= $abortLink ?>"><?= $icons['close'] ?></a>
        </div>
        <form class="payment-form" id="payment-form" action="<?= $url ?>" method="post">
            <?= $csrf; ?>
            <div class="payment-details">
                <p class="payment-text payment-text-bold"><?= Module::t('stripe_item_list_title'); ?></p>
                <div class="payment-items">
                    <?php foreach ($productItems as $item): ?>
                    <div class="payment-item">
                        <div class="payment-item-name">
                            <p class="payment-text"><?= $item['qty']; ?>x <?= Html::encode($item['name'], false); ?></p>
                        </div>
                        <div class="payment-item-price">
                            <p class="payment-text"><?= Yii::$app->formatter->asCurrency($item['total_amount']/100, $currency); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($taxItems)): ?>
                <div class="payment-items">
                    <?php foreach ($taxItems as $item): ?>
                    <div class="payment-item">
                        <div class="payment-item-name">
                            <p class="payment-text"><?= $item['qty']; ?>x <?= Html::encode($item['name'], false); ?></p>
                        </div>
                        <div class="payment-item-price">
                            <p class="payment-text"><?= Yii::$app->formatter->asCurrency($item['total_amount']/100, $currency); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($shippingItems)): ?>
                <div class="payment-items">
                <?php foreach ($shippingItems as $item): ?>
                    <div class="payment-item">
                        <div class="payment-item-name">
                            <p class="payment-text"><?= $item['qty']; ?>x <?= Html::encode($item['name'], false); ?></p>
                        </div>
                        <div class="payment-item-price">
                            <p class="payment-text"><?= Yii::$app->formatter->asCurrency($item['total_amount']/100, $currency); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <div class="payment-items">
                    <div class="payment-item">
                        <div class="payment-item-total">
                            <p class="payment-text"><?= Module::t('stripe_items_total'); ?></p>
                        </div>
                        <div class="payment-item-price">
                            <p class="payment-text"><?= Yii::$app->formatter->asCurrency(($totalAmount/100), $currency); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="payment-pay">
                <label class="payment-label payment-text payment-text-bold" for="payment-stripe"><?= Module::t('stripe_card_info'); ?></label>
                <div class="payment-stripe" id="payment-stripe"></div>
                <div class="payment-errors payment-text payment-text-danger" id="payment-errors" role="alert"></div>
                <div class="payment-buttons">
                    <div class="payment-buttons-button">
                        <a class="payment-button" href="<?= $abortLink ?>"><?= $icons['back'] ?><span><?= Module::t('stripe_button_abort'); ?></span></a>
                    </div>
                    <div class="payment-buttons-button">
                        <button class="payment-button payment-button-submit" type="submit" id="payment-button-submit" disabled>
                            <?= $icons['check'] ?>
                            <span><?= Module::t('stripe_button_pay'); ?></span>
                            <div id="payment-button-spinner" class="payment-button-spinner">
                                <svg class="loader" viewBox="25 25 50 50">
                                    <circle class="loader-path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>