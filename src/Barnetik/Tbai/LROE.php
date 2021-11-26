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

        $output = curl_exec($curl);
        // $json = json_decode($output);
        // var_dump($json);
        var_dump($output);

        // var_dump(curl_getinfo($curl));
        extract(curl_getinfo($curl)); // create metrics variables from getinfo
        $appconnect_time = curl_getinfo($curl, CURLINFO_APPCONNECT_TIME); // request this time explicitly
        $downloadduration = number_format($total_time - $starttransfer_time, 9); // format, to get rid of scientific notation
        $namelookup_time = number_format($namelookup_time, 9);
        $metrics = " CURL: $url \n Time: $total_time \n DNS: $namelookup_time \n Connect: $connect_time \n SSL/SSH: $appconnect_time \n PreTransfer: $pretransfer_time \n StartTransfer: $starttransfer_time \n Download duration: $downloadduration \n HTTP Code: $http_code \n Download size: $size_download";
        error_log($metrics);  // write to php-fpm default www-error.log, or append it to same log as above with file_put_contents(<filename>, $metrics, FILE_APPEND
        curl_close($curl);
    }

    private function getOptArray(ApiRequestInterface $apiRequest, $pfxFilePath, $password): array
    {
        $dataFile = tempnam(sys_get_temp_dir(), 'api-request-data-');
        file_put_contents($dataFile, gzencode($apiRequest->data()));
        return [
            CURLOPT_RETURNTRANSFER      => true,
            CURLOPT_HEADER              => false,
            // CURLINFO_HEADER_OUT         => true,
            CURLOPT_HTTPGET             => false,
            CURLOPT_POST                => true,
            CURLOPT_FOLLOWLOCATION      => false,
            CURLOPT_VERBOSE             => true,
            CURLOPT_FOLLOWLOCATION      => true,

            CURLOPT_TIMEOUT             => 300,

            CURLOPT_URL                 => $this->lroe->getSubmitEndpoint(),
            CURLOPT_HTTPHEADER          => $this->lroes->headers($apiRequest, $dataFile),
            CURLOPT_POSTFIELDS          => file_get_contents($dataFile),

            CURLOPT_SSLCERTTYPE         => 'P12',
            CURLOPT_SSLCERT             => $pfxFilePath,
            CURLOPT_SSLCERTPASSWD       => $password
        ];
        unlink($dataFile);
    }
}
