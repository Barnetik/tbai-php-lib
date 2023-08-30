<?php

namespace Barnetik\Tbai;

use Exception;
use PBurggraf\CRC\CRC8\CRC8;

class Qr
{
    private TicketBai $ticketBai;
    private bool $dev;

    public function __construct(TicketBai $ticketBai, bool $dev = false)
    {
        $this->ticketBai = $ticketBai;
        $this->dev = $dev;
    }

    public function ticketbaiIdentifier(): string
    {
        return $this->ticketBai->ticketbaiIdentifier();
    }

    public function qrUrl(): string
    {
        $url = $this->territoryUrl() . '?' . $this->urlQuery();
        return $url . '&cr=' . $this->crc8($url);
    }

    private function crc8(string $data): string
    {
        $crc8 = new CRC8();
        return str_pad(
            (string)$crc8->calculate($data),
            3,
            '0',
            STR_PAD_LEFT
        );
    }

    private function territoryUrl(): string
    {
        switch ($this->ticketBai->territory()) {
            case TicketBai::TERRITORY_ARABA:
                return $this->arabaUrl();
            case TicketBai::TERRITORY_BIZKAIA:
                return $this->bizkaiaUrl();
            case TicketBai::TERRITORY_GIPUZKOA:
                return $this->gipuzkoaUrl();
            default:
                throw new Exception('Wrong territory');
        }
    }

    private function arabaUrl(): string
    {
        if ($this->dev) {
            return 'https://pruebas-ticketbai.araba.eus/tbai/qrtbai/';
        }

        return 'https://ticketbai.araba.eus/tbai/qrtbai/';
    }

    private function bizkaiaUrl(): string
    {
        return 'https://batuz.eus/QRTBAI/';
    }

    private function gipuzkoaUrl(): string
    {
        if ($this->dev) {
            return 'https://tbai.prep.gipuzkoa.eus/qr/';
        }

        return 'https://tbai.egoitza.gipuzkoa.eus/qr/';
    }

    private function urlQuery(): string
    {
        return sprintf(
            'id=%s&s=%s&nf=%s&i=%s',
            rawurlencode($this->ticketbaiIdentifier()),
            rawurlencode($this->ticketBai->series()),
            rawurlencode($this->ticketBai->invoiceNumber()),
            rawurlencode($this->ticketBai->totalAmount())
        );
    }
}
