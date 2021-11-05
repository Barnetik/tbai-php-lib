<?php

namespace Barnetik\Tbai\Subject;

class Recipient
{
    const TAX_ID_TYPE_IFZ = '02';
    const TAX_ID_TYPE_NIF = '02';
    const TAX_ID_TYPE_PASSPORT = '03';
    /**
     * Egoitza dagoen herrialdeak edo lurraldeak emandako nortasun agiri ofiziala
     * Documento oficial de identificación expedido por el país o territorio de residencia
     */
    const TAX_ID_TYPE_NATIONAL_ID = '04';
    const TAX_ID_TYPE_RESIDENCE_CERTIFICATE = '05';
    const TAX_ID_TYPE_OTHER = '06';

    protected string $taxIdType;
    protected string $taxId;
    protected string $name;
    protected string $countryCode;
    protected ?string $postalCode;

    private function __construct()
    {
    }

    public static function createNationalRecipient(string $taxId, string $name, ?string $postalCode = null): self
    {
        $recipient = new self();
        $recipient->taxIdType = self::TAX_ID_TYPE_IFZ;
        $recipient->countryCode = 'ES';

        $recipient->taxId = $taxId;
        $recipient->name = $name;
        $recipient->postalCode = $postalCode;
        return $recipient;
    }

    public static function createGenericRecipient(string $taxId, string $name, ?string $postalCode = null, string $taxIdType, string $countryCode = 'ES'): self
    {
        $recipient = new self();
        $recipient->taxIdType = $taxIdType;
        $recipient->countryCode = $countryCode;

        $recipient->taxId = $taxId;
        $recipient->name = $name;
        $recipient->postalCode = $postalCode;
        return $recipient;
    }

    public function taxIdType(): string
    {
        return $this->taxIdType;
    }

    public function taxId(): string
    {
        return $this->taxId;
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
}
