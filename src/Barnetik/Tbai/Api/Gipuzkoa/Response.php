<?php

namespace Barnetik\Tbai\Api\Gipuzkoa;

use Barnetik\Tbai\Api\Response as ApiResponse;
use SimpleXMLElement;

class Response extends ApiResponse
{
    private SimpleXMLElement $responseContent;

    public function __construct(string $status, array $headers, string $content)
    {
        parent::__construct($status, $headers, $content);

        if ($status == 200) {
            $this->responseContent = new SimpleXMLElement($this->content);
        }
    }

    public function isCorrect(): bool
    {
        if ($this->status != 200) {
            return false;
        }

        return ((string)$this->responseContent->Salida->Estado === '00');
    }

    public function mainErrorMessage(): string
    {
        return json_encode([
            'codigo' => (string)$this->responseContent->Salida->ResultadosValidacion->Codigo,
            'descripcion' => (string)$this->responseContent->Salida->ResultadosValidacion->Descripcion,
            'azalpena' => (string)$this->responseContent->Salida->ResultadosValidacion->Azalpena,
        ]);
    }

    public function content(): string
    {
        return $this->content;
    }
}
