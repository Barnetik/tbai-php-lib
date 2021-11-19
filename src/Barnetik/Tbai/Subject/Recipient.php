<?php

namespace Barnetik\Tbai\Subject;

class Recipient
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

    private function __construct()
    {
    }

    public static function createNationalRecipient(string $vatId, string $name, ?string $postalCode = null): self
    {
        $recipient = new self();
        $recipient->vatIdType = self::VAT_ID_TYPE_IFZ;
        $recipient->countryCode = 'ES';

        $recipient->vatId = $vatId;
        $recipient->name = $name;
        $recipient->postalCode = $postalCode;
        return $recipient;
    }

    public static function createGenericRecipient(string $vatId, string $name, ?string $postalCode = null, string $vatIdType, string $countryCode = 'ES'): self
    {
        $recipient = new self();
        $recipient->vatIdType = $vatIdType;
        $recipient->countryCode = $countryCode;

        $recipient->vatId = $vatId;
        $recipient->name = $name;
        $recipient->postalCode = $postalCode;
        return $recipient;
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
}
