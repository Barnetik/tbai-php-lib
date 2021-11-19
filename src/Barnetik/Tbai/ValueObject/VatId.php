<?php

namespace Barnetik\Tbai\ValueObject;

use Barnetik\Tbai\Exception\InvalidVatIdException;
use Stringable;

class VatId implements Stringable
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

    private string $value;
    private string $type;

    public function __construct(string $vatId, string $type = self::VAT_ID_TYPE_IFZ)
    {
        $this->check($vatId, $type);
        $this->type = $type;
        $this->value = $vatId;
    }

    public function check(string $vatId, string $type): bool
    {
        if (!in_array($type, $this->validIdTypes())) {
            throw new InvalidVatIdException('Wrong VatId Type');
        }

        if ($type === self::VAT_ID_TYPE_NIF && !preg_match('/^(([a-z|A-Z]{1}\d{7}[a-z|A-Z]{1})|(\d{8}[a-z|A-Z]{1})|([a-z|A-Z]{1}\d{8}))$/', $vatId, $matches)) {
            throw new InvalidVatIdException('Wrong VATId provided');
        }

        return true;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    protected function validIdTypes(): array
    {
        return [
            self::VAT_ID_TYPE_IFZ,
            self::VAT_ID_TYPE_NIF,
            self::VAT_ID_TYPE_PASSPORT,
            self::VAT_ID_TYPE_NATIONAL_ID,
            self::VAT_ID_TYPE_RESIDENCE_CERTIFICATE,
            self::VAT_ID_TYPE_OTHER
        ];
    }

    public function type(): string
    {
        return $this->type;
    }
}
