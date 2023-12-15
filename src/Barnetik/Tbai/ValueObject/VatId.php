<?php

namespace Barnetik\Tbai\ValueObject;

use Barnetik\Tbai\Exception\InvalidVatIdException;
use Barnetik\Tbai\Interfaces\Stringable;

class VatId implements Stringable
{
    const VAT_ID_TYPE_IFZ = '02';
    const VAT_ID_TYPE_NIF = '02';
    const VAT_ID_TYPE_EUVAT = '02';
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
        $this->checkType($type);
        $this->type = $type;
        $this->value = $vatId;
    }

    private function checkType(string $type): bool
    {
        if (!in_array($type, self::validIdTypeValues())) {
            throw new InvalidVatIdException('Wrong VatId Type provided');
        }

        return true;
    }

    public function check(string $vatId, string $type): bool
    {
        trigger_error(
            'Deprecated. This only checks if type is correct and will be removed on the future as NIF/IFZ type MUST be used for intracomunitary transactions',
            E_USER_DEPRECATED
        );

        return $this->checkType($type);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function validIdTypeValues(): array
    {
        return [
            self::VAT_ID_TYPE_IFZ,
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
