<?php

namespace Barnetik\Tbai\LROE\Expenses\SelfEmployed;

use Barnetik\Tbai\LROE\Expenses\Issuer;
use DOMDocument;
use DOMNode;

class DeclaredSupplierCounterpart extends Issuer
{
    public function xml(DOMDocument $domDocument): DOMNode
    {
        $issuer = $domDocument->createElement('ContraparteDeclaradaProveedora');
        if ($this->hasNifAsVatId() && $this->isNational()) {
            $issuer->appendChild(
                $domDocument->createElement('NIF', $this->vatId())
            );
        } else {
            $otherId = $domDocument->createElement('IDOtro');
            $otherId->appendChild($domDocument->createElement('CodigoPais', $this->countryCode()));
            $otherId->appendChild($domDocument->createElement('IDType', $this->vatIdType()));

            $vatId = (string)$this->vatId();
            if ($this->hasNifAsVatId() && substr($vatId, 0, 2) !== $this->countryCode()) {
                $vatId = $this->countryCode . $vatId;
            }
            $otherId->appendChild($domDocument->createElement('ID', $vatId));

            $issuer->appendChild(
                $otherId
            );
        }

        $issuer->appendChild(
            $domDocument->createElement('ApellidosNombreRazonSocial', htmlspecialchars($this->name, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'))
        );

        return $issuer;
    }
}
