<?php

namespace Barnetik\Tbai\Api\Araba;

use Barnetik\Tbai\Api\AbstractResponse as ApiResponse;
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

    public function isDelivered(): bool
    {
        if ($this->status != 200) {
            return false;
        }

        return ((string)$this->responseContent->Salida->Estado === '00');
    }

    public function isCorrect(): bool
    {
        if (!$this->isDelivered()) {
            return false;
        }

        if ($this->responseContent->Salida->ResultadosValidacion) {
            return false;
        }

        return true;
    }

    public function mainErrorMessage(): string
    {
        if ($this->status != 200) {
            return json_encode([
                'codigo' => $this->status
            ]);
        }
        $result = [];
        foreach ($this->responseContent->Salida->ResultadosValidacion as $validacion) {
            $result[] = [
                'codigo' => (string)$validacion->Codigo,
                'azalpena' => (string)$validacion->Azalpena,
                'descripcion' => (string)$validacion->Descripcion,
            ];
        }
        return json_encode($result);
    }

    public function registryErrorData(): array
    {
        if ($this->status != 200) {
            return [];
        }

        $result = [];
        foreach ($this->responseContent->Salida->ResultadosValidacion as $validacion) {
            $result[] = [
                'errorCode' => (string)$validacion->Codigo,
                'errorMessage' => [
                    'eu' => (string)$validacion->Azalpena,
                    'es' => (string)$validacion->Descripcion,
                ],
            ];
        }
        return $result;
    }

    public function content(): string
    {
        return $this->content;
    }
}
