<?php

namespace luya\payment\providers;

use Curl\Curl;
use luya\payment\base\Provider;
use luya\payment\PaymentException;
use luya\payment\base\ProviderInterface;
use luya\payment\transactions\SaferPayTransaction;

/**
 * Safer Pay Provider
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 * @deprecated Deprectated since version 2.0 will be removed in version 4.0. The SaferPay HTTPS interface will shutdown on December 2020.
 */
class SaferPayLegacyProvider extends Provider implements ProviderInterface
{
    public $mode;

    public function getId()
    {
        return 'saferpay';
    }

    public function getBaseUrl()
    {
        if ($this->mode === SaferPayTransaction::MODE_LIVE) {
            return 'https://www.saferpay.com/';
        }

        return 'https://test.saferpay.com/';
    }

    public function callCreate($accountId, $amount, $currency, $description, $orderId, $successLink, $failLink, $backLink, $notifyUrl)
    {
        $curl = new Curl();
        $curl->post($this->getBaseUrl() . 'hosting/CreatePayInit.asp', [
            'ACCOUNTID' => $accountId,
            'AMOUNT' => $amount,
            'CURRENCY' => $currency,
            'DESCRIPTION' => $description,
            'ORDERID' => $orderId,
            'SUCCESSLINK' => $successLink,
            'FAILLINK' => $failLink,
            'BACKLINK' => $backLink,
            'NOTIFYURL' => $notifyUrl,
            'AUTOCLOSE' => '0',
        ]);
        
        if (!$curl->error) {
            return $curl->response;
        }
        
        throw new PaymentException($curl->error_message);
    }
    
    public function callConfirm($data, $signature)
    {
        $curl = new Curl();
        $curl->post($this->getBaseUrl() . 'hosting/VerifyPayConfirm.asp', [
            'DATA' => $data,
            'SIGNATURE' => $signature,
        ]);
        
        if (!$curl->error) {
            return $curl->response;
        }
        
        throw new PaymentException("payconfirm error");
    }
    
    public function callComplete($id, $token, $amount, $action, $accountId, $spPassword = null)
    {
        $data = [
            'ID' => $id,
            'TOKEN' => $token,
            'AMOUNT' => $amount,
            'ACTION' => $action,
            'ACCOUNTID' => $accountId,
        ];
        
        if (!empty($spPassword)) {
            $data['spPassword'] = $spPassword;
        }
        
        $curl = new Curl();
        $curl->post($this->getBaseUrl() . 'hosting/PayCompleteV2.asp', $data);
        
        if (!$curl->error) {
            return $curl->response;
        }
        
        throw new PaymentException("payconfirm error");
    }
}
