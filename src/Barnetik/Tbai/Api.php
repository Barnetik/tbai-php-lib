<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Api\ApiRequestInterface;
use Barnetik\Tbai\Exception\InvalidEndpointException;
use Barnetik\Tbai\Exception\UnsignedException;
use Barnetik\Tbai\Api\Araba\Endpoint as ArabaEndpoint;
use Barnetik\Tbai\Api\Bizkaia\Endpoint as BizkaiaEndpoint;
use Barnetik\Tbai\Api\EndpointInterface;
use Barnetik\Tbai\Api\Gipuzkoa\Endpoint as GipuzkoaEndpoint;
use Barnetik\Tbai\Api\Response;

class Api
{
    const ENDPOINT_ARABA = 'araba';
    const ENDPOINT_BIZKAIA = 'bizkaia';
    const ENDPOINT_GIPUZKOA = 'gipuzkoa';

    const DEBUG_SENT_FILE = 'sentFile';

    private EndpointInterface $endpoint;

    public function __construct(string $endpoint, bool $dev = false, bool $debug = false)
    {
        switch ($endpoint) {
            case self::ENDPOINT_ARABA:
                $this->endpoint = new ArabaEndpoint($dev, $debug);
                break;
            case self::ENDPOINT_BIZKAIA:
                $this->endpoint = new BizkaiaEndpoint($dev, $debug);
                break;
            case self::ENDPOINT_GIPUZKOA:
                $this->endpoint = new GipuzkoaEndpoint($dev, $debug);
                break;
            default:
                throw new InvalidEndpointException();
        }
    }

    public function submitInvoice(TicketBai $ticketbai, string $pfxFilePath, string $password): Response
    {
        if (!$ticketbai->isSigned()) {
            throw new UnsignedException();
        }

        return $this->endpoint->submitInvoice($ticketbai, $pfxFilePath, $password);
    }

    public function debugData(string $key): mixed
    {
        return $this->endpoint->debugData($key);
    }
}
