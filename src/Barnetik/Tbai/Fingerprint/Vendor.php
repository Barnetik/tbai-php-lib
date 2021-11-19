<?php

namespace Barnetik\Tbai\Fingerprint;

use Barnetik\Tbai\Interface\TbaiXml;
use DOMDocument;
use DOMNode;

class Vendor implements TbaiXml
{
    const NAME = 'TBAI Zubia';
    const VERSION = '1.0-dev';

    private string $license;
    private string $developerId;

    public function __construct(string $license, string $developerId)
    {
        $this->license = $license;
        $this->developerId = $developerId;
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
            $domDocument->createElement('Nombre', self::NAME),
            $domDocument->createElement('Version', self::VERSION)
        );
        return $vendor;
    }
}
