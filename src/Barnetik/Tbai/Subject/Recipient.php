<?php

namespace Barnetik\Tbai\Subject;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\TypeChecker\VatId;
use DOMDocument;
use DOMNode;
use InvalidArgumentException;

class Recipient implements TbaiXml
{
    const VAT_ID_TYPE_IFZ = '02';
    const VAT_ID_TYPE_NIF = '02';
    const VAT_ID_TYPE_PASSPORT = '03';
    /**
     * Egoitza dagoen herrialdeak edo lurraldeak emandako nortasun agiri ofiziala
     * Documento oficial de identificación expedido por el país o territorio de residencia
     */
    const VAT_ID_TYPE_NATIONAL_ID = '04';
    const VAT_ID_TYPE_RESIDENCE_CERTIFICATE = '05';
    const VAT_ID_TYPE_OTHER = '06';

    protected string $vatIdType;
    protected string $vatId;
    protected string $name;
    protected string $countryCode;
    protected ?string $postalCode;
    protected ?string $address = null;

    protected VatId $vatIdChecker;

    private function __construct()
    {
        $this->vatIdChecker = new VatId();
    }

    public static function createNationalRecipient(string $vatId, string $name, ?string $postalCode = null): self
    {
        $recipient = new self();
        $recipient->setVatId(self::VAT_ID_TYPE_IFZ, $vatId);
        $recipient->countryCode = 'ES';
        $recipient->name = $name;
        $recipient->postalCode = $postalCode;
        return $recipient;
    }

    public static function createGenericRecipient(string $vatId, string $name, ?string $postalCode = null, string $vatIdType = self::VAT_ID_TYPE_NIF, string $countryCode = 'ES'): self
    {
        $recipient = new self();
        $recipient->setVatId($vatIdType, $vatId);

        $recipient->countryCode = $countryCode;
        $recipient->name = $name;
        $recipient->postalCode = $postalCode;
        return $recipient;
    }

    protected function setVatId(string $vatIdType, string $vatId): self
    {
        if (!in_array($vatIdType, $this->validIdTypes())) {
            throw new InvalidArgumentException('Wrong VatId Type');
        }

        if ($vatIdType === self::VAT_ID_TYPE_NIF) {
            $this->vatIdChecker->check($vatId);
        }
        $this->vatIdType = $vatIdType;
        $this->vatId = $vatId;

        return $this;
    }

    protected function validIdTypes(): array
    {
        return [
            self:: VAT_ID_TYPE_IFZ,
            self:: VAT_ID_TYPE_NIF,
            self:: VAT_ID_TYPE_PASSPORT,
            self:: VAT_ID_TYPE_NATIONAL_ID,
            self:: VAT_ID_TYPE_RESIDENCE_CERTIFICATE,
            self:: VAT_ID_TYPE_OTHER
        ];
    }

    public function vatIdType(): string
    {
        return $this->vatIdType;
    }

    public function vatId(): string
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

    public function countryCode(): string
    {
        return $this->countryCode;
    }

    protected function hasNifAsVatId(): bool
    {
        return $this->vatIdType() === self::VAT_ID_TYPE_NIF;
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
            $otherId->append(
                $domDocument->createElement('CodigoPais', $this->countryCode()),
                $domDocument->createElement('IDType', $this->vatIdType()),
                $domDocument->createElement('ID', $this->vatId())
            );

            $recipient->appendChild(
                $otherId
            );
        }

        $recipient->appendChild(
            $domDocument->createElement('ApellidosNombreRazonSocial', $this->name)
        );

        if ($this->postalCode()) {
            $recipient->appendChild(
                $domDocument->createElement('CodigoPostal', $this->postalCode())
            );
        }

        if ($this->address) {
            $recipient->appendChild(
                $domDocument->createElement('Direccion', $this->address)
            );
        }
        return $recipient;
    }
}
