<?php

namespace Barnetik\Tbai\Api\Bizkaia;

use Barnetik\Tbai\Api\AbstractResponse as ApiResponse;
use DOMDocument;
use DOMXPath;
use SimpleXMLElement;

class Response extends ApiResponse
{
    private SimpleXMLElement $responseContent;

    public function __construct(string $status, array $headers, string $content)
    {
        parent::__construct($status, $headers, $content);

        if ($status == 200) {
            $this->responseContent = new SimpleXMLElement($this->content());
        }
    }

    public function isDelivered(): bool
    {
        if ($this->status != 200) {
            return false;
        }

        return $this->isCorrect();
    }

    public function isCorrect(): bool
    {
        return $this->headers['eus-bizkaia-n3-tipo-respuesta'] !== 'Incorrecto';
    }

    public function mainErrorMessage(): string
    {
        return $this->headers['eus-bizkaia-n3-mensaje-respuesta'];
    }

    public function content(): string
    {
        if ($this->content) {
            return gzdecode($this->content);
        }

        return '';
    }

    public function registryErrorData(): array
    {
        if ($this->status != 200) {
            return [];
        }
        $result = [];
        foreach ($this->responseContent->Registros->Registro as $registro) {
            $result[] = [
                'errorCode' => (string)$registro->SituacionRegistro->CodigoErrorRegistro,
                'errorMessage' => [
                    'eu' => (string)$registro->SituacionRegistro->DescripcionErrorRegistroEU,
                    'es' => (string)$registro->SituacionRegistro->DescripcionErrorRegistroES,
                ],
            ];
        }
        return $result;
    }
}
