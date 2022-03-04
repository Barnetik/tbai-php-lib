<?php

namespace Barnetik\Tbai;

use Exception;
use PBurggraf\CRC\CRC8\CRC8;

class Qr
{
    private TicketBai $ticketBai;

    public function __construct(TicketBai $ticketBai)
    {
        $this->ticketBai = $ticketBai;
    }

    public function ticketbaiIdentifier(): string
    {
        $code = sprintf(
            'TBAI-%s-%s-%s',
            $this->ticketBai->issuerVatId(),
            $this->ticketBai->expeditionDate()->short(),
            $this->ticketBai->shortSignatureValue()
        );

        return $code . '-' . $this->crc8($code);
        ;
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
        return 'https://ticketbai.araba.eus/tbai/qrtbai/';
    }

    private function bizkaiaUrl(): string
    {
        return 'https://batuz.eus/QRTBAI/';
    }

    private function gipuzkoaUrl(): string
    {
        return 'https://tbai.egoitza.gipuzkoa.eus/qr/';
    }

    private function urlQuery(): string
    {
        return sprintf(
            'id=%s&s=%s&nf=%s&i=%s',
            $this->ticketbaiIdentifier(),
            $this->ticketBai->series(),
            $this->ticketBai->invoiceNumber(),
            $this->ticketBai->totalAmount()
        );
    }
}
