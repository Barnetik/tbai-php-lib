<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Exception\InvalidEndpointException;
use Barnetik\Tbai\Exception\UnsignedException;
use Barnetik\Tbai\Api\Araba\Endpoint as ArabaEndpoint;
use Barnetik\Tbai\Api\Bizkaia\Endpoint as BizkaiaEndpoint;
use Barnetik\Tbai\Api\EndpointInterface;
use Barnetik\Tbai\Api\Gipuzkoa\Endpoint as GipuzkoaEndpoint;
use Barnetik\Tbai\Api\ResponseInterface;

class Api
{
    const DEBUG_SENT_FILE = 'sentFile';

    private EndpointInterface $endpoint;

    public function __construct(string $territory, bool $dev = false, bool $debug = false)
    {
        switch ($territory) {
            case TicketBai::TERRITORY_ARABA:
                $this->endpoint = new ArabaEndpoint($dev, $debug);
                break;
            case TicketBai::TERRITORY_BIZKAIA:
                $this->endpoint = new BizkaiaEndpoint($dev, $debug);
                break;
            case TicketBai::TERRITORY_GIPUZKOA:
                $this->endpoint = new GipuzkoaEndpoint($dev, $debug);
                break;
            default:
                throw new InvalidEndpointException();
        }
    }

    public static function createForTicketBai(TicketBai $ticketbai, bool $dev = false, bool $debug = false): self
    {
        return new Api($ticketbai->territory(), $dev, $debug);
    }

    public function submitInvoice(TicketBai $ticketbai, PrivateKey $privateKey, string $password): ResponseInterface
    {
        if (!$ticketbai->isSigned()) {
            throw new UnsignedException();
        }

        return $this->endpoint->submitInvoice($ticketbai, $privateKey, $password);
    }

    public function cancelInvoice(TicketBaiCancel $ticketbaiCancel, PrivateKey $privateKey, string $password): ResponseInterface
    {
        if (!$ticketbaiCancel->isSigned()) {
            throw new UnsignedException();
        }

        return $this->endpoint->cancelInvoice($ticketbaiCancel, $privateKey, $password);
    }

    /**
     *
     * @return mixed
     */
    public function debugData(string $key)
    {
        return $this->endpoint->debugData($key);
    }

    public function endpoint(): EndpointInterface
    {
        return $this->endpoint;
    }
}
