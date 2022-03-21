<?php

namespace Barnetik\Tbai\Api;

use Barnetik\Tbai\TicketBai;

interface ApiRequestInterface
{
    // public function __construct(TicketBai $ticketbai, string $endpoint);
    public function jsonDataHeader(): string;
    public function data(): string;
    public function url(): string;
}
