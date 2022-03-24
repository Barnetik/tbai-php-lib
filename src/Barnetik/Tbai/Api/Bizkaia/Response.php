<?php

namespace Barnetik\Tbai\Api\Bizkaia;

use Barnetik\Tbai\Api\Response as ApiResponse;

class Response extends ApiResponse
{
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
        return gzdecode($this->content);
    }
}
