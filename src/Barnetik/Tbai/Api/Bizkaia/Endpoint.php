<?php

namespace Barnetik\Tbai\Api\Bizkaia;

use Barnetik\Tbai\Api\ApiRequestInterface;
use Barnetik\Tbai\Api\AbstractTerritory;
use Barnetik\Tbai\Api\ResponseInterface;
use Barnetik\Tbai\Exception\InvalidTerritoryException;
use Barnetik\Tbai\LROE\Expenses\Interfaces\ExpensesInvoice as InterfacesExpensesInvoice;
use Barnetik\Tbai\LROE\Expenses\SelfEmployed\ExpensesWithoutInvoice;
use Barnetik\Tbai\PrivateKey;
use Barnetik\Tbai\TicketBai;
use Barnetik\Tbai\TicketBaiCancel;
use Barnetik\Tbai\Zuzendu;
use Barnetik\Tbai\ZuzenduCancel;

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
        throw new InvalidTerritoryException('This territory does not implement Zuzendu services.');
    }

    public function createCancelZuzenduRequest(ZuzenduCancel $zuzenduCancel): ApiRequestInterface
    {
        throw new InvalidTerritoryException('This territory does not implement Zuzendu services.');
    }

    protected function response(string $status, array $headers, string $content): Response
    {
        return new Response($status, $headers, $content);
    }

    public function createSubmitExpensesInvoiceRequest(InterfacesExpensesInvoice $expenses): ApiRequestInterface
    {
        return new SubmitExpensesInvoiceRequest($expenses, $this->getSubmitEndpoint());
    }

    public function submitExpensesInvoice(InterfacesExpensesInvoice $expenses, PrivateKey $privateKey, string $password, int $maxRetries = 1, int $retryDelay = 1): ResponseInterface
    {
        $submitExpensesRequest = $this->createSubmitExpensesInvoiceRequest($expenses);
        return $this->doRequest($submitExpensesRequest, $privateKey, $password, $maxRetries, $retryDelay);
    }

    public function createSubmitExpensesWithoutInvoiceRequest(ExpensesWithoutInvoice $expenses): ApiRequestInterface
    {
        return new SubmitExpensesWithoutInvoiceRequest($expenses, $this->getSubmitEndpoint());
    }

    public function submitExpensesWithoutInvoice(ExpensesWithoutInvoice $expenses, PrivateKey $privateKey, string $password, int $maxRetries = 1, int $retryDelay = 1): ResponseInterface
    {
        $submitExpensesRequest = $this->createSubmitExpensesWithoutInvoiceRequest($expenses);
        return $this->doRequest($submitExpensesRequest, $privateKey, $password, $maxRetries, $retryDelay);
    }
}
