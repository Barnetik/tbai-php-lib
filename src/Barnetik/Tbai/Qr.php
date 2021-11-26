<?php

namespace Barnetik\Tbai;

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

        return $code . '-' . $this->getCrc8($code);
        ;
    }

    public function qrUrl(): string
    {
        $url = $this->getBizkaiaUrl() . '?' . $this->getUrlQuery();
        return $url . '&crc=' . $this->getCrc8($url);
    }

    private function getCrc8(string $data): int
    {
        $crc8 = new CRC8();
        return $crc8->calculate($data);
    }

    private function getBizkaiaUrl(): string
    {
        return 'https://batuz.eus/QRTBAI/';
    }

    private function getUrlQuery(): string
    {
        return sprintf(
            'id=%s&s=%s&nf=%s&i=%s',
            $this->ticketbaiIdentifier(),
            $this->ticketBai->series(),
            $this->ticketBai->invoiceNumber(),
            $this->ticketBai->totalAmmount()
        );
    }
}
