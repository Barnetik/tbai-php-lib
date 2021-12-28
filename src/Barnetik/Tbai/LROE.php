<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Api\ApiRequestInterface;
use Barnetik\Tbai\Api\SubmitInvoiceRequest;
use Barnetik\Tbai\Exception\InvalidEndpointException;
use Barnetik\Tbai\Exception\UnsignedException;
use Barnetik\Tbai\LROE\AbstractTerritory;
use Barnetik\Tbai\LROE\Araba;
use Barnetik\Tbai\LROE\Bizkaia;
use Barnetik\Tbai\LROE\Gipuzkoa;

class LROE
{
    const ENDPOINT_ARABA = 'araba';
    const ENDPOINT_BIZKAIA = 'bizkaia';
    const ENDPOINT_GIPUZKOA = 'gipuzkoa';

    private AbstractTerritory $lroe;

    public function __construct(string $endpoint, bool $dev = false)
    {
        switch ($endpoint) {
            case self::ENDPOINT_ARABA:
                $this->lroe = new Araba($dev);
                break;
            case self::ENDPOINT_BIZKAIA:
                $this->lroe = new Bizkaia($dev);
                break;
            case self::ENDPOINT_GIPUZKOA:
                $this->lroe = new Gipuzkoa($dev);
                break;
            default:
                throw new InvalidEndpointException();
        }
    }

    public function submitInvoice(TicketBai $ticketBai, string $pfxFilePath, string $password): void
    {
        if (!$ticketBai->isSigned()) {
            throw new UnsignedException();
        }

        $curl = curl_init();
        $submitInvoiceRequest = new SubmitInvoiceRequest($ticketBai);
        curl_setopt_array($curl, $this->getOptArray($submitInvoiceRequest, $pfxFilePath, $password));
        curl_setopt($curl, CURLOPT_STDERR, fopen(__DIR__ . '/curl.log', 'w+'));

        $response = curl_exec($curl);
        list($headers, $content) = $this->parseCurlResponse($response);

        // var_dump($headers);
        // var_dump(curl_getinfo($curl));
        $curlInfo = curl_getinfo($curl);
        extract($curlInfo); // create metrics variables from getinfo
        $appconnect_time = curl_getinfo($curl, CURLINFO_APPCONNECT_TIME); // request this time explicitly
        $downloadduration = number_format($total_time - $starttransfer_time, 9); // format, to get rid of scientific notation
        $namelookup_time = number_format($namelookup_time, 9);
        $metrics = [
            "CURL: $url",
            "Time: $total_time",
            "DNS: $namelookup_time",
            "Connect: $connect_time",
            "SSL/SSH: $appconnect_time",
            "PreTransfer: $pretransfer_time",
            "StartTransfer: $starttransfer_time",
            "Download duration: $downloadduration ",
            "HTTP Code: $http_code",
            "Download size: $size_download",
        ];
        error_log(implode("\n", $metrics));
        curl_close($curl);
    }

    private function parseCurlResponse(string $response): array
    {

        list($rawHeaders, $content) = explode("\r\n\r\n", $response, 2);

        $expHeaders = explode("\r\n", $rawHeaders);

        $headers = [];
        foreach ($expHeaders as $currentHeaderData) {
            list($key, $value) = explode(": ", $currentHeaderData, 2);
            $headers[$key] = $value;
        }

        return [$headers, $content];
    }

    private function getOptArray(ApiRequestInterface $apiRequest, string $pfxFilePath, string $password): array
    {
        $dataFile = tempnam(sys_get_temp_dir(), 'api-request-data-');
        file_put_contents($dataFile, gzencode($apiRequest->data()));

        $data = [
            CURLOPT_RETURNTRANSFER      => true,
            CURLOPT_HEADER              => true,
            // CURLINFO_HEADER_OUT         => true,
            CURLOPT_HTTPGET             => false,
            CURLOPT_POST                => true,
            CURLOPT_VERBOSE             => true,
            CURLOPT_FOLLOWLOCATION      => true,

            CURLOPT_TIMEOUT             => 300,

            CURLOPT_URL                 => $this->lroe->getSubmitEndpoint(),
            CURLOPT_HTTPHEADER          => $this->lroe->headers($apiRequest, $dataFile),
            CURLOPT_POSTFIELDS          => file_get_contents($dataFile),

            CURLOPT_SSLCERTTYPE         => 'P12',
            CURLOPT_SSLCERT             => $pfxFilePath,
            CURLOPT_SSLCERTPASSWD       => $password
        ];

        unlink($dataFile);
        return $data;
    }
}
