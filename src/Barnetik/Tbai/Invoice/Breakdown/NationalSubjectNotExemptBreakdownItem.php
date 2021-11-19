<?php

namespace Barnetik\Tbai\Invoice\Breakdown;

use Barnetik\Tbai\TypeChecker\Ammount;
use Barnetik\Tbai\Exception\InvalidNotExemptTypeException;
use OutOfBoundsException;

class NationalSubjectNotExemptBreakdownItem
{
    const NOT_EXEMPT_TYPE_S1 = 'S1';
    const NOT_EXEMPT_TYPE_S2 = 'S2';

    private string $notExemptType;
    private string $taxBase;
    private array $vatDetails = [];
    private Ammount $ammountChecker;

    public function __construct(string $taxBase, string $type)
    {
        $this->ammountChecker = new Ammount();
        $this->setTaxBase($taxBase);
        $this->setNotExemptType($type);
    }

    private function validNotExemptTypes(): array
    {
        return [
            self::NOT_EXEMPT_TYPE_S1,
            self::NOT_EXEMPT_TYPE_S2,
        ];
    }

    private function setTaxBase(string $taxBase): self
    {
        $this->ammountChecker->check($taxBase);
        $this->taxBase = $taxBase;
        return $this;
    }

    private function setNotExemptType(string $type): self
    {
        if (!in_array($type, $this->validNotExemptTypes())) {
            throw new InvalidNotExemptTypeException();
        }
        $this->notExemptType = $type;

        return $this;
    }

    public function addVatDetail(VatDetail $vatDetail): self
    {
        if (sizeof($this->vatDetails) < 6) {
            $this->vatDetails[] = $vatDetail;
            return $this;
        }

        throw new OutOfBoundsException('Too many vat detail items');
    }
}
