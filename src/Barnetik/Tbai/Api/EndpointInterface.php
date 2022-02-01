<?php
namespace Barnetik\Tbai\Api;

use Barnetik\Tbai\TicketBai;

interface EndpointInterface {
    public function __construct(bool $dev = false);
    public function createSubmitInvoiceRequest(TicketBai $ticketBai): ApiRequestInterface;
    public function headers(ApiRequestInterface $apiRequest, string $dataFile): array;
}