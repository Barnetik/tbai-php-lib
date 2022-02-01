<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Api\ApiRequestInterface;
use Barnetik\Tbai\Exception\InvalidEndpointException;
use Barnetik\Tbai\Exception\UnsignedException;
use Barnetik\Tbai\Api\Araba\Endpoint as ArabaEndpoint;
use Barnetik\Tbai\Api\Bizkaia\Endpoint as BizkaiaEndpoint;
use Barnetik\Tbai\Api\EndpointInterface;
use Barnetik\Tbai\Api\Gipuzkoa\Endpoint as GipuzkoaEndpoint;
use Barnetik\Tbai\Api\Response;

class Api
{
    const ENDPOINT_ARABA = 'araba';
    const ENDPOINT_BIZKAIA = 'bizkaia';
    const ENDPOINT_GIPUZKOA = 'gipuzkoa';

    const DEBUG_SENT_FILE = 'sentFile';

    private EndpointInterface $endpoint;
    private bool $debug = false;
    private array $debugData = [
        self::DEBUG_SENT_FILE => null
    ];

    public function __construct(string $endpoint, bool $dev = false, bool $debug = false)
    {
        $this->debug = $debug;
        switch ($endpoint) {
            case self::ENDPOINT_ARABA:
                $this->endpoint = new ArabaEndpoint($dev);
                break;
            case self::ENDPOINT_BIZKAIA:
                $this->endpoint = new BizkaiaEndpoint($dev);
                break;
            case self::ENDPOINT_GIPUZKOA:
                $this->endpoint = new GipuzkoaEndpoint($dev);
                break;
            default:
                throw new InvalidEndpointException();
        }
    }

    public function submitInvoice(TicketBai $ticketbai, string $pfxFilePath, string $password): Response
    {
        if (!$ticketbai->isSigned()) {
            throw new UnsignedException();
        }

        $curl = curl_init();
        $submitInvoiceRequest = $this->endpoint->createSubmitInvoiceRequest($ticketbai);
        curl_setopt_array($curl, $this->getOptArray($submitInvoiceRequest, $pfxFilePath, $password));
        // curl_setopt($curl, CURLOPT_STDERR, fopen(__DIR__ . '/curl.log', 'w+'));

        $response = curl_exec($curl);
        list($headers, $content) = $this->parseCurlResponse($response);
        curl_close($curl);
        return new Response($headers, $content);
    }

    private function parseCurlResponse(string $response): array
    {

        list($rawHeaders, $content) = explode("\r\n\r\n", $response, 2);

        $expHeaders = explode("\r\n", $rawHeaders);

        $headers = [];
        foreach ($expHeaders as $currentHeaderData) {
            $data = explode(": ", $currentHeaderData, 2);
            if (sizeof($data) === 2) {
                $headers[$data[0]] = $data[1];
            }
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
            // CURLOPT_VERBOSE             => true,
            CURLOPT_FOLLOWLOCATION      => true,

            CURLOPT_TIMEOUT             => 300,

            CURLOPT_URL                 => $apiRequest->getSubmitEndpoint(),
            CURLOPT_HTTPHEADER          => $this->endpoint->headers($apiRequest, $dataFile),
            CURLOPT_POSTFIELDS          => file_get_contents($dataFile),

            CURLOPT_SSLCERTTYPE         => 'P12',
            CURLOPT_SSLCERT             => $pfxFilePath,
            CURLOPT_SSLCERTPASSWD       => $password
        ];

        if ($this->debug) {
            $this->debugData[self::DEBUG_SENT_FILE] = $dataFile;
        } else {
            unlink($dataFile);
        }
        return $data;
    }

    public function debugData(string $key): mixed {
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