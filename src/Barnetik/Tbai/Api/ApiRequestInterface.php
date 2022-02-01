<?php

namespace Barnetik\Tbai\Api;

use Barnetik\Tbai\TicketBai;

interface ApiRequestInterface
{
    public function __construct(TicketBai $ticketbai, string $endpoint = null);
    public function jsonDataHeader(): string;
    public function data(): string;
    public function getSubmitEndpoint(): string;
}
