<?php
namespace Barnetik\Tbai;

class InvoiceLine
{
    const TAX_TYPE_4 = 4;
    const TAX_TYPE_10 = 10;
    const TAX_TYPE_21 = 21;

    protected $description;
    protected $beforeTaxPrice;
    protected $afterTaxPrice;
    protected $taxType;

    public function __construct($description, $beforeTaxPrice, $afterTaxPrice, $taxType)
    {
        $this->description = $description;
        $this->beforeTaxPrice = $beforeTaxPrice;
        $this->afterTaxPrice = $afterTaxPrice;
        $this->taxType = $taxType;
    }

	public function description()
	{
		return $this->description;
	}

	public function beforeTaxPrice()
	{
		return $this->beforeTaxPrice;
	}

	public function afterTaxPrice()
	{
		return $this->afterTaxPrice;
	}

	public function taxType()
	{
		return $this->taxType;
	}
}