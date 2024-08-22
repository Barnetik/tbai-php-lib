<?php

namespace Barnetik\Tbai\LROE\Expenses;

use Barnetik\Tbai\LROE\Expenses\Interfaces\ExpensesInvoice as InterfacesExpensesInvoice;
use Barnetik\Tbai\LROE\Expenses\JuridicPerson\ExpensesInvoice as JuridicPersonExpensesInvoice;
use Barnetik\Tbai\LROE\Expenses\SelfEmployed\ExpensesInvoice as SelfEmployedExpensesInvoice;

class ExpensesInvoiceFactory
{
    public static function createFromJson(array $jsonData): InterfacesExpensesInvoice
    {
        if (array_key_exists('selfEmployed', $jsonData) && $jsonData['selfEmployed']) {
            return SelfEmployedExpensesInvoice::createFromJson($jsonData);
        }

        return JuridicPersonExpensesInvoice::createFromJson($jsonData);
    }
}
