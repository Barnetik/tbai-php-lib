<?php

namespace Barnetik\Tbai\LROE\Expenses\Interfaces;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Date;

interface ExpensesInvoice extends TbaiXml
{
    public function selfEmployed(): bool;
    public function receptionDate(): Date;
    public function recipientVatId(): string;
    public function recipientName(): string;
}
