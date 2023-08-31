<?php

namespace Barnetik\Tbai\Api\Bizkaia;

use Barnetik\Tbai\Api\AbstractResponse as ApiResponse;
use SimpleXMLElement;

class Response extends ApiResponse
{
    private ?SimpleXMLElement $responseContent = null;

    public function __construct(string $status, array $headers, string $content)
    {
        parent::__construct($status, $headers, $content);

        if ($status == 200 && $this->content()) {
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
        return array_key_exists('eus-bizkaia-n3-tipo-respuesta', $this->headers)
            && $this->headers['eus-bizkaia-n3-tipo-respuesta'] !== 'Incorrecto';
    }

    public function mainErrorMessage(): string
    {
        if (array_key_exists('eus-bizkaia-n3-mensaje-respuesta', $this->headers)) {
            return $this->headers['eus-bizkaia-n3-mensaje-respuesta'];
        }

        return '';
    }

    public function content(): string
    {
        if ($this->content) {
            return gzdecode($this->content);
        }

        return '';
    }

    public function errorDataRegistry(): array
    {
        if ($this->status != 200) {
            return [];
        }

        $result = [];
        if ($this->responseContent) {
            foreach ($this->responseContent->Registros->Registro as $registro) {
                if ($registro->SituacionRegistro->CodigoErrorRegistro) {
                    $result[] = [
                        'errorCode' => (string)$registro->SituacionRegistro->CodigoErrorRegistro,
                        'errorMessage' => [
                            'eu' => (string)$registro->SituacionRegistro->DescripcionErrorRegistroEU,
                            'es' => (string)$registro->SituacionRegistro->DescripcionErrorRegistroES,
                        ],
                    ];
                }
            }
        }

        return $result;
    }

    public function hasErrorData(): bool
    {
        return sizeof($this->errorDataRegistry()) > 0;
    }
}
