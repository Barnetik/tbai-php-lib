<?php

namespace Barnetik\Tbai\LROE\Expenses;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\VatId;
use DOMDocument;
use DOMNode;

class Issuer implements TbaiXml
{
    protected VatId $vatId;
    protected string $name;
    protected string $countryCode;

    private function __construct()
    {
    }

    public static function createNationalIssuer(VatId $vatId, string $name): self
    {
        $issuer = new self();
        $issuer->vatId = $vatId;

        $issuer->countryCode = 'ES';
        $issuer->name = $name;
        return $issuer;
    }

    public static function createGenericIssuer(VatId $vatId, string $name, string $countryCode = 'ES'): self
    {
        $issuer = new self();
        $issuer->vatId = $vatId;

        $issuer->countryCode = $countryCode;
        $issuer->name = $name;
        return $issuer;
    }

    private function vatIdType(): string
    {
        return $this->vatId->type();
    }

    private function vatId(): VatId
    {
        return $this->vatId;
    }

    private function name(): string
    {
        return $this->name;
    }

    private function countryCode(): string
    {
        return $this->countryCode;
    }

    protected function hasNifAsVatId(): bool
    {
        return $this->vatIdType() === VatId::VAT_ID_TYPE_NIF;
    }

    protected function isNational(): bool
    {
        return $this->countryCode() === 'ES';
    }

    public static function createFromJson(array $jsonData): self
    {
        $countryCode = $jsonData['countryCode'] ?? 'ES';
        $name = $jsonData['name'];
        if ($countryCode === 'ES') {
            $vatId = new VatId($jsonData['vatId']);
            $issuer = self::createNationalIssuer($vatId, $name);
        } else {
            $vatId = new VatId($jsonData['vatId'], $jsonData['vatIdType']);
            $issuer = self::createGenericIssuer($vatId, $name, $countryCode);
        }
        return $issuer;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $issuer = $domDocument->createElement('EmisorFacturaRecibida');
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

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'vatId' => [
                    'type' => 'string',
                    'description' => 'IFZ edo Identifikatzailea - NIF o Identificador'
                ],
                'vatIdType' => [
                    'type' => 'string',
                    'enum' => VatId::validIdTypeValues(),
                    'default' => '02',
                    'description' => '
Dokumentu mota - Tipo de documento:
 * 02: IFZ - NIF
 * 03: Pasaportea - Pasaporte
 * 04: Egoitza dagoen herrialdeak edo lurraldeak emandako nortasun agiri ofiziala - Documento oficial de identificación expedido por el país o territorio de residencia
 * 05: Egoitza ziurtagiria - Certificado de residencia
 * 06: Beste frogagiri bat - Otro documento probatorio
                    '
                ],
                'name' => [
                    'type' => 'string',
                    'maxLength' => 120,
                    'description' => 'Abizenak eta izena edo Sozietatearen izena - Apellidos y nombre o Razón social'
                ],
                'countryCode' => [
                    'type' => 'string',
                    'description' => 'Herrialdearen kodea (ISO3166 alpha2) - Código de país (ISO3166 alpha2)',
                    'default' => 'ES'
                ]
            ],
            'required' => ['vatId', 'name']
        ];
    }

    public function toArray(): array
    {
        return [
            'vatId' => (string)$this->vatId(),
            'vatIdType' => $this->vatId()->type(),
            'name' => $this->name(),
            'countryCode' => $this->countryCode(),
        ];
    }
}
