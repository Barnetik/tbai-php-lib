<?php

namespace Barnetik\Tbai\Fingerprint;

use Barnetik\Tbai\Interfaces\TbaiXml;
use DOMDocument;
use DOMNode;
use DOMXPath;

class Vendor implements TbaiXml
{
    const NAME = 'TBAI Zubia';
    const VERSION = '1.0-dev';

    private string $license;
    private string $developerId;
    private string $name;
    private string $version;

    public function __construct(string $license, string $developerId, string $name = null, string $version = null)
    {
        $this->license = $license;
        $this->developerId = $developerId;
        $this->name = $name ?? self::NAME;
        $this->version = $version ?? self::VERSION;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $vendor = $domDocument->createElement('Software');
        $developer = $domDocument->createElement('EntidadDesarrolladora');
        $developer->appendChild(
            $domDocument->createElement('NIF', $this->developerId)
        );

        $vendor->appendChild($domDocument->createElement('LicenciaTBAI', $this->license));
        $vendor->appendChild($developer);
        $vendor->appendChild($domDocument->createElement('Nombre', $this->name));
        $vendor->appendChild($domDocument->createElement('Version', $this->version));

        return $vendor;
    }

    public static function createFromXml(DOMXPath $xpath): self
    {
        $license = $xpath->evaluate('string(/T:TicketBai/HuellaTBAI/Software/LicenciaTBAI)');
        $name = $xpath->evaluate('string(/T:TicketBai/HuellaTBAI/Software/Nombre)');
        $version = $xpath->evaluate('string(/T:TicketBai/HuellaTBAI/Software/Version)');
        $nif = $xpath->evaluate('string(/T:TicketBai/HuellaTBAI/Software/EntidadDesarrolladora/NIF)');

        return new self($license, $nif, $name, $version);
    }
}
