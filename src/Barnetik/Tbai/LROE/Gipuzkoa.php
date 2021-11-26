<?php

namespace Barnetik\Tbai\LROE;

use Barnetik\Tbai\Api\ApiRequestInterface;

class Gipuzkoa extends AbstractTerritory
{
    const SUBMIT_ENDPOINT_DEV = 'https://tbai-z.egoitza.gipuzkoa.eus/sarrerak/alta';
    const SUBMIT_ENDPOINT = 'https://tbai-z.egoitza.gipuzkoa.eus/sarrerak/alta';

    public function headers(ApiRequestInterface $apiRequest, string $dataFile): array
    {
        return [
            'Content-Type: application/xml;charset=UTF-8'
        ];
    }
}
