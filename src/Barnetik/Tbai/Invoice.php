<?php
namespace Barnetik\Tbai;

class Invoice
{
    const TAX_TYPE_4 = 4;
    const TAX_TYPE_10 = 10;
    const TAX_TYPE_21 = 21;

    protected $invoiceNumber;
    protected $description;

    protected $beforeTaxTotal;
    protected $afterTaxTotal;
    protected $taxType;

    protected $lines = [];

    public function __construct($invoiceNumber, $description, $beforeTaxTotal, $afterTaxTotal, $taxType)
    {
        $this->invoiceNumber = $invoiceNumber;
        $this->description = $description;

        $this->beforeTaxTotal = $beforeTaxTotal;
        $this->afterTaxTotal = $afterTaxTotal;
        $this->taxType = $taxType;
    }

    public function addLine(InvoiceLine $line)
    {
        array_push($this->lines, $line);
        return $this;
    }

	public function invoiceNumber()
	{
		return $this->invoiceNumber;
	}

	public function description()
	{
		return $this->description;
	}

	public function beforeTaxTotal()
	{
		return $this->beforeTaxTotal;
	}

	public function afterTaxTotal()
	{
		return $this->afterTaxTotal;
	}

	public function taxType()
	{
		return $this->taxType;
	}

	public function lines()
	{
		return $this->lines;
	}
}