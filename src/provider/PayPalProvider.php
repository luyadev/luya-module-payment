<?php

namespace luya\payment\provider;

use Yii;
use luya\payment\base\Provider;
use luya\payment\base\ProviderInterface;
use luya\payment\PaymentException;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\ItemList;
use PayPal\Api\Item;
use luya\payment\transaction\PayPalTransaction;
use PayPal\Api\Details;

/**
 * @todo Unable to generate auth token with curl library: https://github.com/php-mod/curl
 * @author nadar
 */
class PayPalProvider extends Provider implements ProviderInterface
{
    /**
     * @var string string The mode of the api context `live` or `sandbox`.
     */
    public $mode;
    
    public function getId()
    {
        return 'paypal';
    }

    public function getConfigArray()
    {
        $config = ['mode' => $this->mode];

        if ($this->mode == PayPalTransaction::MODE_SANDBOX) {
            $config['log.LogEnabled'] = true;
            $config['log.FileName'] = Yii::getAlias('@runtime/PayPal.log');
            $config['log.LogLevel'] = 'DEBUG';
        }

        return $config;
    }
    
    public function callCreate($clientId, $clientSecret, $orderId, $amount, $currency, $description, $returnUrl, $cancelUrl, array $items, array $taxes, array $shipping)
    {
        $oauthCredential = new OAuthTokenCredential($clientId, $clientSecret);
        
        $apiContext = new ApiContext($oauthCredential);
        $apiContext->setConfig($this->getConfigArray());
        
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        
        // $items
        /*
        $item = new Item();
        $item->setName($description);
        $item->setCurrency($currency);
        $item->setPrice($amount);
        $item->setQuantity(1);
        */
        $products = [];
        $itemTotalAmount = 0;
        foreach ($items as $item) {
            $i = new Item();
            $i->setName($item['name']);
            $i->setCurrency($currency);
            $i->setPrice(PayPalTransaction::floatAmount($item['amount']));
            $i->setQuantity($item['qty']);
            $products[] = $i;
            $itemTotalAmount += $item['total_amount'];
        }

        $taxAmount = 0;
        foreach ($taxes as $tax) {
            $taxAmount += $tax['amount'];
        }

        $shippingAmount = 0;
        foreach ($shipping as $ship) {
            $shippingAmount += $ship['amount'];
        }

        $details = false;
        if ($shippingAmount || $taxAmount) {
            $details = new Details();
            if ($shippingAmount) {
                $details->setShipping(PayPalTransaction::floatAmount($shippingAmount));
            }
            if ($taxAmount) {
                $details->setTax(PayPalTransaction::floatAmount($taxAmount));
            }
            $details->setSubtotal(PayPalTransaction::floatAmount($itemTotalAmount));
        }

        
        $itemList = new ItemList();
        $itemList->setItems($products);
        
        $amountObject = new Amount();
        $amountObject->setCurrency($currency);
        $amountObject->setTotal(PayPalTransaction::floatAmount($amount));
        if ($details) {
            $amountObject->setDetails($details);
        }
        
        $transaction = new Transaction();
        $transaction->setItemList($itemList);
        $transaction->setAmount($amountObject);
        $transaction->setDescription($description);
        $transaction->setInvoiceNumber($orderId);
        
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($returnUrl)->setCancelUrl($cancelUrl);
        
        $payment = new Payment();
        $payment->setIntent('sale');
        $payment->setPayer($payer);
        $payment->setRedirectUrls($redirectUrls);
        $payment->setTransactions(array($transaction));
        
        try {
            $payment->create($apiContext);
        } catch (\Exception $e) {
            throw new PaymentException('PayPal Exception: '. $e->getMessage());
        }
        
        $approvalUrl = $payment->getApprovalLink();
        
        return $approvalUrl;
    }
    
    public function callExecute($clientId, $clientSecret, $paymentId, $payerId, $amount, $currency)
    {
        $oauthCredential = new OAuthTokenCredential($clientId, $clientSecret);
        
        $apiContext = new ApiContext($oauthCredential);
        $apiContext->setConfig($this->getConfigArray());
        
        $payment = Payment::get($paymentId, $apiContext);
        
        $execution = new PaymentExecution();
        $execution->setPayerId($payerId);
        
        $result = $payment->execute($execution, $apiContext);
        
        try {
            $payment = Payment::get($paymentId, $apiContext);
        } catch (\Exception $e) {
            throw new PaymentException('unable to find payment: ' . $e->getMessage());
        }

        if ($payment->state == 'approved') {
            return true;
        }
        
        return false;
    }
}
