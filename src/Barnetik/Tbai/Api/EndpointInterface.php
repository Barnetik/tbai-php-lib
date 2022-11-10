<?php

namespace Barnetik\Tbai\Api;

use Barnetik\Tbai\PrivateKey;
use Barnetik\Tbai\TicketBai;
use Barnetik\Tbai\TicketBaiCancel;
use Barnetik\Tbai\Zuzendu;
use Barnetik\Tbai\ZuzenduCancel;

interface EndpointInterface
{
    public function __construct(bool $dev = false);
    public function headers(ApiRequestInterface $apiRequest, string $dataFile): array;

    /**
     *
     * @return mixed
     */
    public function debugData(string $key);

    public function submitInvoice(TicketBai $ticketbai, PrivateKey $privateKey, string $password, int $maxRetries = 1, int $retryDelay = 1): ResponseInterface;
    public function createSubmitInvoiceRequest(TicketBai $ticketBai): ApiRequestInterface;

    public function cancelInvoice(TicketBaiCancel $ticketbaiCancel, PrivateKey $privateKey, string $password, int $maxRetries = 1, int $retryDelay = 1): ResponseInterface;
    public function createCancelInvoiceRequest(TicketBaiCancel $ticketBai): ApiRequestInterface;

    public function submitZuzendu(Zuzendu $zuzendu, PrivateKey $privateKey, string $password, int $maxRetries = 1, int $retryDelay = 1): ResponseInterface;
    public function createSubmitZuzenduRequest(Zuzendu $zuzendu): ApiRequestInterface;

    public function cancelZuzendu(ZuzenduCancel $zuzenduCancel, PrivateKey $privateKey, string $password, int $maxRetries = 1, int $retryDelay = 1): ResponseInterface;
    public function createCancelZuzenduRequest(ZuzenduCancel $zuzenduCancel): ApiRequestInterface;
}
