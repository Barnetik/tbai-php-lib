<?php

namespace Barnetik\Tbai\Api;

use Barnetik\Tbai\PrivateKey;
use Barnetik\Tbai\TicketBai;
use Barnetik\Tbai\TicketBaiCancel;

interface EndpointInterface
{
    public function __construct(bool $dev = false);
    public function headers(ApiRequestInterface $apiRequest, string $dataFile): array;

    /**
     *
     * @return mixed
     */
    public function debugData(string $key);

    public function submitInvoice(TicketBai $ticketbai, PrivateKey $privateKey, string $password): ResponseInterface;
    public function createSubmitInvoiceRequest(TicketBai $ticketBai): ApiRequestInterface;

    public function cancelInvoice(TicketBaiCancel $ticketbaiCancel, PrivateKey $privateKey, string $password): ResponseInterface;
    public function createCancelInvoiceRequest(TicketBaiCancel $ticketBai): ApiRequestInterface;
}
