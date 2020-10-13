<?php

namespace luya\payment\providers;

use Curl\Curl;
use luya\helpers\Json;
use luya\payment\base\PayModel;
use luya\payment\base\Provider;
use luya\payment\PaymentException;
use luya\payment\transactions\SaferPayTransaction;

/**
 * @since 3.0
 */
class SaferPayProvider extends Provider
{
    const PRODUCTION_URL = 'https://www.saferpay.com/api';

    const TEST_URL = 'https://test.saferpay.com/api';

    public $specVersion = "1.19";

    /**
     * @var SaferPayTransaction
     */
    public $transaction;

    public function getId()
    {
        return 'saferpay';
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function initialize($uniqueRequestId, PayModel $payModel, $description)
    {
        return $this->generateCurl('/Payment/v1/PaymentPage/Assert', [
            "RequestHeader" => [
                "SpecVersion" => $this->specVersion,
                "CustomerId"  => $this->transaction->customerId,
                "RequestId" => $uniqueRequestId,
                "RetryIndicator"  => 0
            ],
            "TerminalId"  => $this->transaction->terminalId,
            "Payment"  => [
                "Amount" => [
                    "Value"  => $payModel->getTotalAmount(),
                    "CurrencyCode"  => strtoupper($payModel->getCurrency()),
                ],
                "OrderId"  => $payModel->getOrderId(),
                "Description"  => $description,
            ],
            "ReturnUrls" => [
                "Success" => $payModel->getTransactionGatewayBackLink(),
                "Fail" => $payModel->getTransactionGatewayFailLink(),
                "Abort" => $payModel->getTransactionGatewayAbortLink(),
            ],
            "Notification" => [
                "NotifyUrl" => $payModel->getTransactionGatewayNotifyLink(),
            ]
        ]);
    }

    public function assert($uniqueRequestId, $token)
    {
        return $this->generateCurl('/Payment/v1/PaymentPage/Assert', [
            "RequestHeader" => [
                "SpecVersion" => $this->specVersion,
                "CustomerId" => $this->transaction->customerId,
                "RequestId" => $uniqueRequestId,
                "RetryIndicator" => 0,
            ],
            'Token' => $token,
        ]);
    }

    public function capture($uniqueRequestId, $transactionId)
    {
        return $this->generateCurl('/Payment/v1/Transaction/Capture', [
            "RequestHeader" => [
                "SpecVersion" => $this->specVersion,
                "CustomerId" => $this->transaction->customerId,
                "RequestId" => $uniqueRequestId,
                "RetryIndicator" => 0,
            ],
            "TransactionReference" => [
                "TransactionId" => $transactionId,
            ]
        ]);
    }

    public function generateUrl($url)
    {
        if ($this->transaction->mode == SaferPayTransaction::MODE_LIVE) {
            return self::PRODUCTION_URL . $url;
        }

        return self::TEST_URL . $url;
    }

    public function generateAuthCode()
    {
        return base64_encode("{$this->transaction->username}:{$this->transaction->password}");
    }

    /**
     * Undocumented function
     *
     * @param [type] $url
     * @param array $values
     * @return array
     */
    public function generateCurl($url, array $values)
    {
        $curl = new Curl();
        $curl->setOpt(CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Basic '. $this->generateAuthCode()]);
        $curl->post($this->generateUrl($url), $values, true);

        if ($curl->error) {
            throw new PaymentException($curl->error_message);
        }

        if ($curl->curl_error) {
            throw new PaymentException($curl->curl_error_message);
        }

        return Json::decode($curl->response);
    }
}