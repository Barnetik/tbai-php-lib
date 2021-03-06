<?php

namespace Barnetik\Tbai\Api;

use Barnetik\Tbai\Api\ApiRequestInterface;
use Barnetik\Tbai\PrivateKey;
use Barnetik\Tbai\TicketBai;
use Barnetik\Tbai\TicketBaiCancel;
use Exception;

abstract class AbstractTerritory implements EndpointInterface
{
    const SUBMIT_ENDPOINT = '';
    const SUBMIT_ENDPOINT_DEV = '';

    const DEBUG_SENT_FILE = 'sentFile';

    protected bool $dev;
    protected bool $debug;

    private array $debugData = [
        self::DEBUG_SENT_FILE => null
    ];

    public function __construct(bool $dev = false, bool $debug = false)
    {
        $this->dev = $dev;
        $this->debug = $debug;
    }

    protected function getSubmitEndpoint(): string
    {
        if ($this->dev) {
            return static::SUBMIT_ENDPOINT_DEV;
        }

        return static::SUBMIT_ENDPOINT;
    }

    abstract public function headers(ApiRequestInterface $apiRequest, string $dataFile): array;
    abstract public function createSubmitInvoiceRequest(TicketBai $ticketBai): ApiRequestInterface;
    abstract public function createCancelInvoiceRequest(TicketBaiCancel $ticketBaiCancel): ApiRequestInterface;
    abstract protected function response(string $status, array $headers, string $content): ResponseInterface;

    public function submitInvoice(TicketBai $ticketbai, PrivateKey $privateKey, string $password): ResponseInterface
    {
        $curl = curl_init();
        $submitInvoiceRequest = $this->createSubmitInvoiceRequest($ticketbai);
        curl_setopt_array($curl, $this->getOptArray($submitInvoiceRequest, $privateKey, $password));

        $response = curl_exec($curl);
        list($status, $headers, $content) = $this->parseCurlResponse($response, $curl);
        curl_close($curl);
        return $this->response($status, $headers, $content);
    }

    public function cancelInvoice(TicketBaiCancel $ticketbaiCancel, PrivateKey $privateKey, string $password): ResponseInterface
    {
        $curl = curl_init();
        $submitInvoiceRequest = $this->createCancelInvoiceRequest($ticketbaiCancel);
        curl_setopt_array($curl, $this->getOptArray($submitInvoiceRequest, $privateKey, $password));

        $response = curl_exec($curl);
        list($status, $headers, $content) = $this->parseCurlResponse($response, $curl);
        curl_close($curl);
        return $this->response($status, $headers, $content);
    }

    /** @phpstan-ignore-next-line */
    protected function parseCurlResponse(string $response, $curlHandle): array
    {
        if (!$response) {
            throw new Exception("No response from server");
        }

        list($rawHeaders, $content) = explode("\r\n\r\n", $response, 2);
        $expHeaders = explode("\r\n", $rawHeaders);
        $headers = [];
        foreach ($expHeaders as $currentHeaderData) {
            $data = explode(": ", $currentHeaderData, 2);
            if (sizeof($data) === 2) {
                $headers[$data[0]] = $data[1];
            }
        }

        $status = curl_getinfo($curlHandle, CURLINFO_RESPONSE_CODE);
        return [$status, $headers, $content];
    }

    protected function getOptArray(ApiRequestInterface $apiRequest, PrivateKey $privateKey, string $password): array
    {
        $dataFile = tempnam(sys_get_temp_dir(), 'api-request-data-');
        file_put_contents($dataFile, $apiRequest->data());

        $data = [
            CURLOPT_RETURNTRANSFER      => true,
            CURLOPT_HEADER              => true,
            // CURLINFO_HEADER_OUT         => true,
            CURLOPT_HTTPGET             => false,
            CURLOPT_POST                => true,
            // CURLOPT_VERBOSE             => true,
            CURLOPT_FOLLOWLOCATION      => true,

            CURLOPT_TIMEOUT             => 20,

            CURLOPT_URL                 => $apiRequest->url(),
            CURLOPT_HTTPHEADER          => $this->headers($apiRequest, $dataFile),
            CURLOPT_POSTFIELDS          => file_get_contents($dataFile),
        ];

        if ($privateKey->type() === PrivateKey::TYPE_P12) {
            $data += [
                CURLOPT_SSLCERTTYPE         => 'P12',
                CURLOPT_SSLCERT             => $privateKey->keyPath(),
                CURLOPT_SSLCERTPASSWD       => $password
            ];
        } else {
            $data += [
                CURLOPT_SSLCERTTYPE         => 'PEM',
                CURLOPT_SSLCERT             => $privateKey->certPath(),
                CURLOPT_SSLKEY             => $privateKey->keyPath(),
                CURLOPT_SSLCERTPASSWD       => $password
            ];
        }

        if ($this->debug) {
            $this->debugData[self::DEBUG_SENT_FILE] = $dataFile;
        } else {
            unlink($dataFile);
        }
        return $data;
    }

    /**
     *
     * @return mixed
     */
    public function debugData(string $key)
    {
        return $this->debugData[$key];
    }

    // private function logInfo($curl)
    // {
    //     $curlInfo = curl_getinfo($curl);
    //     extract($curlInfo); // create metrics variables from getinfo
    //     $appconnect_time = curl_getinfo($curl, CURLINFO_APPCONNECT_TIME); // request this time explicitly
    //     $downloadduration = number_format($total_time - $starttransfer_time, 9); // format, to get rid of scientific notation
    //     $namelookup_time = number_format($namelookup_time, 9);
    //     $metrics = [
    //         "CURL: $url",
    //         "Time: $total_time",
    //         "DNS: $namelookup_time",
    //         "Connect: $connect_time",
    //         "SSL/SSH: $appconnect_time",
    //         "PreTransfer: $pretransfer_time",
    //         "StartTransfer: $starttransfer_time",
    //         "Download duration: $downloadduration ",
    //         "HTTP Code: $http_code",
    //         "Download size: $size_download",
    //     ];
    //     error_log(implode("\n", $metrics));
    // }
}
