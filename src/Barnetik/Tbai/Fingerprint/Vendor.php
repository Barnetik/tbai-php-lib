<?php

namespace Barnetik\Tbai\Fingerprint;

class Vendor
{
    const NAME = 'TBAI Zubia';
    const VERSION = '1.0a';

    private string $license;
    private string $developerId;

    public function __construct(string $license, string $developerId)
    {
        $this->license = $license;
        $this->developerId = $developerId;
    }
}
