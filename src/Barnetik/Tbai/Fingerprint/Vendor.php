<?php

namespace Barnetik\Tbai\Fingerprint;

use Barnetik\Tbai\Interfaces\TbaiXml;
use DOMDocument;
use DOMNode;

class Vendor implements TbaiXml
{
    const NAME = 'TBAI Zubia';
    const VERSION = '1.0-dev';

    private string $license;
    private string $developerId;
    private string $name;
    private string $version;

    public function __construct(string $license, string $developerId, string $name = self::NAME, string $version = self::VERSION)
    {
        $this->license = $license;
        $this->developerId = $developerId;
        $this->name = $name;
        $this->version = $version;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $vendor = $domDocument->createElement('Software');
        $developer = $domDocument->createElement('EntidadDesarrolladora');
        $developer->appendChild(
            $domDocument->createElement('NIF', $this->developerId)
        );
        $vendor->append(
            $domDocument->createElement('LicenciaTBAI', $this->license),
            $developer,
            $domDocument->createElement('Nombre', $this->name),
            $domDocument->createElement('Version', $this->version)
        );
        return $vendor;
    }
}
