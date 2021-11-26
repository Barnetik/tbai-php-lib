<?php

namespace Barnetik\Tbai\LROE;

use Barnetik\Tbai\Api\ApiRequestInterface;

class Bizkaia extends AbstractTerritory
{
    const SUBMIT_ENDPOINT_DEV = 'https://pruesarrerak.bizkaia.eus/N3B4000M/aurkezpena';
    const SUBMIT_ENDPOINT = 'https://pruesarrerak.bizkaia.eus/N3B4000M/aurkezpena';

    public function headers(ApiRequestInterface $apiRequest, string $dataFile): array
    {
        return [
            'Accept-Encoding: gzip',
            'Content-Encoding: gzip',
            'Content-Length: ' . filesize($dataFile),
            'Content-Type: application/octet-stream',
            'eus-bizkaia-n3-version: 1.0',
            'eus-bizkaia-n3-content-type: application/xml',
            'eus-bizkaia-n3-data: ' . $apiRequest->jsonDataHeader(),
        ];
    }
}
