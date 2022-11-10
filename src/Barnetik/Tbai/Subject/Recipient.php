<?php

namespace Barnetik\Tbai\Subject;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\VatId;
use DOMDocument;
use DOMNode;
use DOMXPath;

class Recipient implements TbaiXml
{
    protected VatId $vatId;
    protected string $name;
    protected string $countryCode;
    protected string $postalCode;
    protected string $address;

    private function __construct()
    {
    }

    public static function createNationalRecipient(VatId $vatId, string $name, string $postalCode, string $address): self
    {
        $recipient = new self();
        $recipient->vatId = $vatId;
        // $recipient->setVatId(self::VAT_ID_TYPE_IFZ, $vatId);

        $recipient->countryCode = 'ES';
        $recipient->name = $name;
        $recipient->postalCode = $postalCode;
        $recipient->address = $address;
        return $recipient;
    }

    public static function createGenericRecipient(VatId $vatId, string $name, string $postalCode, string $address, string $countryCode = 'ES'): self
    {
        $recipient = new self();
        $recipient->vatId = $vatId;

        $recipient->countryCode = $countryCode;
        $recipient->name = $name;
        $recipient->postalCode = $postalCode;
        $recipient->address = $address;
        return $recipient;
    }

    public function vatIdType(): string
    {
        return $this->vatId->type();
    }

    public function vatId(): VatId
    {
        return $this->vatId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function postalCode(): string
    {
        return (string) $this->postalCode;
    }

    public function address(): string
    {
        return (string) $this->address;
    }

    public function countryCode(): string
    {
        return $this->countryCode;
    }

    protected function hasNifAsVatId(): bool
    {
        return $this->vatIdType() === VatId::VAT_ID_TYPE_NIF;
    }

    public static function createFromJson(array $jsonData): self
    {
        $countryCode = $jsonData['countryCode'] ?? 'ES';
        $name = $jsonData['name'];
        $postalCode = $jsonData['postalCode'];
        $address = $jsonData['address'];
        if ($countryCode === 'ES') {
            $vatId = new VatId($jsonData['vatId']);
            $recipient = self::createNationalRecipient($vatId, $name, $postalCode, $address);
        } else {
            $vatId = new VatId($jsonData['vatId'], $jsonData['vatIdType']);
            $recipient = self::createGenericRecipient($vatId, $name, $postalCode, $address, $countryCode);
        }
        return $recipient;
    }

    public static function createFromXml(DOMXPath $xpath, DOMNode $contextNode): self
    {
        $name = $xpath->evaluate('string(ApellidosNombreRazonSocial)', $contextNode);
        $postalCode = $xpath->evaluate('string(CodigoPostal)', $contextNode);
        $address = $xpath->evaluate('string(Direccion)', $contextNode);

        if ($xpath->evaluate('count(NIF)', $contextNode) === 0) {
            $vatIdValue = $xpath->evaluate('string(IDOtro/ID)', $contextNode);
            $vatType = $xpath->evaluate('string(IDOtro/IDType)', $contextNode);
            $countryCode = $xpath->evaluate('string(IDOtro/CodigoPais)', $contextNode);

            $vatId = new VatId($vatIdValue, $vatType);
            $recipient = self::createGenericRecipient($vatId, $name, $postalCode, $address, $countryCode);
        } else {
            $vatId = new VatId($xpath->evaluate('string(NIF)', $contextNode));
            $recipient = self::createNationalRecipient($vatId, $name, $postalCode, $address);
        }

        return $recipient;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $recipient = $domDocument->createElement('IDDestinatario');
        if ($this->hasNifAsVatId()) {
            $recipient->appendChild(
                $domDocument->createElement('NIF', $this->vatId())
            );
        } else {
            $otherId = $domDocument->createElement('IDOtro');
            $otherId->appendChild($domDocument->createElement('CodigoPais', $this->countryCode()));
            $otherId->appendChild($domDocument->createElement('IDType', $this->vatIdType()));
            $otherId->appendChild($domDocument->createElement('ID', $this->vatId()));

            $recipient->appendChild(
                $otherId
            );
        }

        $recipient->appendChild(
            $domDocument->createElement('ApellidosNombreRazonSocial', htmlspecialchars($this->name, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'))
        );

        if ($this->postalCode()) {
            $recipient->appendChild(
                $domDocument->createElement('CodigoPostal', htmlspecialchars($this->postalCode(), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'))
            );
        }

        if ($this->address()) {
            $recipient->appendChild(
                $domDocument->createElement('Direccion', htmlspecialchars($this->address(), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'))
            );
        }
        return $recipient;
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
                'postalCode' => [
                    'type' => 'string',
                    'maxLength' => 20
                ],
                'address' => [
                    'type' => 'string',
                    'maxLength' =>  250

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
            'postalCode' => $this->postalCode(),
            'address' => $this->address(),
            'countryCode' => $this->countryCode(),
        ];
    }
}
