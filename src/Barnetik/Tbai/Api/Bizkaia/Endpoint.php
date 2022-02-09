<?php

namespace Barnetik\Tbai\Api\Bizkaia;

use Barnetik\Tbai\Api\ApiRequestInterface;
use Barnetik\Tbai\Api\AbstractTerritory;
use Barnetik\Tbai\TicketBai;

class Endpoint extends AbstractTerritory
{
    const SUBMIT_ENDPOINT_DEV = 'https://pruesarrerak.bizkaia.eus';
    const SUBMIT_ENDPOINT = 'https://sarrerak.bizkaia.eus';

    public function headers(ApiRequestInterface $apiRequest, string $dataFile): array
    {
        return [
            'Accept-Encoding: gzip',
            'Content-Encoding: gzip',
            'Content-Length: ' . filesize($dataFile),
            'Content-Type: application/octet-stream',
            'eus-bizkaia-n3-version: 1.0',
            'eus-bizkaia-n3-content-type: application/xml',
            'eus-bizkaia-n3-data: ' . $apiRequest->jsonDataHeader(),
        ];
    }

    public function createSubmitInvoiceRequest(TicketBai $ticketBai): ApiRequestInterface
    {
        return new SubmitInvoiceRequest($ticketBai, $this->getSubmitEndpoint());
    }

    public function submitInvoice(TicketBai $ticketbai, string $pfxFilePath, string $password): Response
    {
        $curl = curl_init();
        $submitInvoiceRequest = $this->createSubmitInvoiceRequest($ticketbai);
        curl_setopt_array($curl, $this->getOptArray($submitInvoiceRequest, $pfxFilePath, $password));

        $response = curl_exec($curl);
        list($status, $headers, $content) = $this->parseCurlResponse($response);
        curl_close($curl);
        return new Response($status, $headers, $content);
    }

    protected function response(string $status, array $headers, string $content): Response
    {
        return new Response($status, $headers, $content);
    }
}
