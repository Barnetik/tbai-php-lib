<?php

namespace Barnetik\Tbai\Api;

use Barnetik\Tbai\SubmitInvoiceFile;

interface EndpointInterface
{
    public function __construct(bool $dev = false);
    public function createSubmitInvoiceRequest(SubmitInvoiceFile $ticketBai): ApiRequestInterface;
    public function headers(ApiRequestInterface $apiRequest, string $dataFile): array;
    public function submitInvoice(SubmitInvoiceFile $ticketbai, string $pfxFilePath, string $password): Response;
    public function debugData(string $key): mixed;
}
