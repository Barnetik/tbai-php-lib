<?php

namespace Barnetik\Tbai\Api\Araba;

use Barnetik\Tbai\Api\ApiRequestInterface;
use Barnetik\Tbai\Api\AbstractTerritory;
use Barnetik\Tbai\TicketBai;
use Barnetik\Tbai\TicketBaiCancel;
use Barnetik\Tbai\Zuzendu;
use Barnetik\Tbai\ZuzenduCancel;

class Endpoint extends AbstractTerritory
{
    const SUBMIT_ENDPOINT_DEV = 'https://pruebas-ticketbai.araba.eus/TicketBAI/v1';
    const SUBMIT_ENDPOINT = 'https://ticketbai.araba.eus/TicketBAI/v1';

    public function headers(ApiRequestInterface $apiRequest, string $dataFile): array
    {
        return [
            'Content-Type: application/xml;charset=UTF-8',
            'Content-Length: ' . filesize($dataFile),
            'Expect: '
        ];
    }

    public function createSubmitInvoiceRequest(TicketBai $ticketBai): ApiRequestInterface
    {
        return new SubmitInvoiceRequest($ticketBai, $this->getSubmitEndpoint());
    }

    public function createCancelInvoiceRequest(TicketBaiCancel $ticketBaiCancel): ApiRequestInterface
    {
        return new CancelInvoiceRequest($ticketBaiCancel, $this->getSubmitEndpoint());
    }

    public function createSubmitZuzenduRequest(Zuzendu $zuzendu): ApiRequestInterface
    {
        return new SubmitZuzenduRequest($zuzendu, $this->getSubmitEndpoint());
    }

    public function createCancelZuzenduRequest(ZuzenduCancel $zuzenduCancel): ApiRequestInterface
    {
        return new CancelZuzenduRequest($zuzenduCancel, $this->getSubmitEndpoint());
    }

    protected function response(string $status, array $headers, string $content): Response
    {
        return new Response($status, $headers, $content);
    }
}
