<?php

namespace Barnetik\Tbai\LROE;

use Barnetik\Tbai\Api\ApiRequestInterface;

abstract class AbstractTerritory
{
    const SUBMIT_ENDPOINT = '';
    const SUBMIT_ENDPOINT_DEV = '';

    protected bool $dev;

    public function __construct(bool $dev = false)
    {
        $this->dev = $dev;
    }

    public function getSubmitEndpoint(): string
    {
        if ($this->dev) {
            return static::SUBMIT_ENDPOINT_DEV;
        }

        return static::SUBMIT_ENDPOINT;
    }

    abstract public function headers(ApiRequestInterface $apiRequest, string $dataFile): array;
}
