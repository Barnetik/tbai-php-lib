<?php

namespace Barnetik\Tbai\Api\Gipuzkoa;

use Barnetik\Tbai\Api\ApiRequestInterface;
use Barnetik\Tbai\Zuzendu;

class SubmitZuzenduRequest implements ApiRequestInterface
{
    const URL = '/sarrerak/zuzendu-alta';
    protected string $endpoint;
    protected Zuzendu $zuzendu;

    public function __construct(Zuzendu $zuzendu, string $endpoint)
    {
        $this->endpoint = $endpoint;
        $this->zuzendu = $zuzendu;
    }

    public function url(): string
    {
        return $this->endpoint . static::URL;
    }

    public function data(): string
    {
        return $this->zuzendu;
    }

    public function jsonDataHeader(): string
    {
        return json_encode([]);
    }
}
