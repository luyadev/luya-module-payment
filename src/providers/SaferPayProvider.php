<?php

namespace luya\payment\providers;

use Curl\Curl;
use luya\helpers\Json;
use luya\payment\base\PayModel;
use luya\payment\base\Provider;
use luya\payment\PaymentException;
use luya\payment\transactions\SaferPayTransaction;
use Yii;

/**
 * Safer Pay Provider.
 * 
 * @author Basil Suter <git@nadar.io>
 * @since 3.0
 */
class SaferPayProvider extends Provider
{
    const PRODUCTION_URL = 'https://www.saferpay.com/api';

    const TEST_URL = 'https://test.saferpay.com/api';

    /**
     * @var string The api specification version.
     */
    public $specVersion = "1.19";

    /**
     * @var SaferPayTransaction
     */
    public $transaction;

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return 'saferpay';
    }

    /**
     * Initialize the Payment
     *
     * @param string $uniqueRequestId
     * @param PayModel $payModel
     * @param string $description
     * @return array Returns the api response
     */
    public function initialize($uniqueRequestId, PayModel $payModel, $description)
    {
        return $this->generateCurl('/Payment/v1/PaymentPage/Initialize', [
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

    /**
     * Assert the payment
     *
     * @param string $uniqueRequestId
     * @param string $token
     * @return array Returns the api response
     */
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

    /**
     * Capture the payment
     *
     * @param string $uniqueRequestId
     * @param string $transactionId
     * @return array Returns the api response
     */
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

    /**
     * Generate the full api url
     *
     * @param string $url
     * @return string
     */
    public function generateUrl($url)
    {
        if ($this->transaction->mode == SaferPayTransaction::MODE_LIVE) {
            return self::PRODUCTION_URL . $url;
        }

        return self::TEST_URL . $url;
    }

    /**
     * Generate the auth code from username and password
     *
     * @return string
     */
    public function generateAuthCode()
    {
        return base64_encode("{$this->transaction->username}:{$this->transaction->password}");
    }

    /**
     * Generate the curl request and return the api response as array
     *
     * @param string $url
     * @param array $values
     * @return array
     * @throws PaymentException
     */
    public function generateCurl($url, array $values)
    {
        $curl = new Curl();
        $curl->setOpt(CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Basic '. $this->generateAuthCode()]);
        $curl->post($this->generateUrl($url), $values, true);

        Yii::debug("curl request for {$url}." . var_export($curl, true), __METHOD__);

        if ($curl->error) {
            throw new PaymentException($curl->error_message . ' | ' . $curl->response);
        }

        if ($curl->curl_error) {
            throw new PaymentException($curl->curl_error_message);
        }

        return Json::decode($curl->response);
    }
}