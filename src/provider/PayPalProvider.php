<?php

namespace luya\payment\provider;

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

/**
 * @todo Unable to generate auth token with curl library: https://github.com/php-mod/curl
 * @author nadar
 */
class PayPalProvider extends Provider implements ProviderInterface
{
    /**
     * @var string string The mode of the api context `live` or `sandbox`.
     */
    public $mode = 'live';
    
    public function getId()
    {
        return 'paypal';
    }
    
    public function callCreate($clientId, $clientSecret, $orderId, $amount, $currency, $description, $returnUrl, $cancelUrl, array $items, array $taxes, array $shipping)
    {
        $oauthCredential = new OAuthTokenCredential($clientId, $clientSecret);
        
        $apiContext = new ApiContext($oauthCredential);
        $apiContext->setConfig([
            'mode' => $this->mode,
        ]);
        
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
        foreach ($items as $item) {
            $i = new Item();
            $i->setName($item['name']);
            $i->setCurrency($currency);
            $i->setPrice($item['amount']);
            $i->setQuantity($item['qty']);
            $products[] = $i;
        }

        
        $itemList = new ItemList();
        $itemList->setItems($products);
        
        $amountObject = new Amount();
        $amountObject->setCurrency($currency);
        $amountObject->setTotal($amount);
        
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
        $apiContext->setConfig([
            'mode' => $this->mode,
        ]);
        
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
