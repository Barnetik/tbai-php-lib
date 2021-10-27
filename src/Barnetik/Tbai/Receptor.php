<?php

namespace Barnetik\Tbai;

class Receptor
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

    protected $taxIdType;
    protected $taxId;
    protected $name;
    protected $postalCode;
    protected $countryCode;

    private function __construct()
    {
    }

    public function createNationalReceptor($taxId, $name, $postalCode = null)
    {
        $receptor = new self;
        $receptor->taxIdType = self::TAX_ID_TYPE_IFZ;
        $receptor->countryCode = 'ES';

        $receptor->taxId = $taxId;
        $receptor->name = $name;
        $receptor->postalCode = $postalCode;
        return $receptor;
    }

    public function createForeignReceptor($taxIdType, $taxId, $name, $countryCode = 'ES', $postalCode = null)
    {
        $receptor = new self;
        $receptor->taxIdType = $taxIdType;
        $receptor->countryCode = $countryCode;

        $receptor->taxId = $taxId;
        $receptor->name = $name;
        $receptor->postalCode = $postalCode;
        return $receptor;
    }

    public function taxIdType()
    {
        return $this->taxIdType;
    }

    public function taxId()
    {
        return $this->taxId;
    }

    public function name()
    {
        return $this->name;
    }

    public function postalCode()
    {
        return $this->postalCode;
    }

    public function countryCode()
    {
        return $this->countryCode;
    }
}
