<?php

use Barnetik\Tbai\LROE\Expenses\JuridicPerson\ExpensesInvoice as JuridicPersonExpensesInvoice;
use Barnetik\Tbai\LROE\Expenses\SelfEmployed\ExpensesInvoice as SelfEmployedExpensesInvoice;
use Barnetik\Tbai\TicketBai;
use Barnetik\Tbai\TicketBaiCancel;

include_once(__DIR__ . '/../vendor/autoload.php');

$docs = [
    "swagger" => "2.0",
    "info" => [
        "description" => "This document describes available JSON schemas for barnetik/ticketbai PHP library",
        "version" => "0.5.0",
        "title" => "Barnetik/Ticketbai JSON schemas"
    ],
    "definitions" => [],
    "externalDocs" => [
        "description" => "Find out more about barnetik/ticketbai",
        "url" => "https://github.com/barnetik/tbai-php-lib"
    ]
];
$docs['definitions']['Ticketbai'] = TicketBai::docJson();
$docs['definitions']['TicketbaiCancel'] = TicketBaiCancel::docJson();
$docs['definitions']['ExpensesInvoice (Juridic Person)'] = JuridicPersonExpensesInvoice::docJson();
$docs['definitions']['ExpensesInvoice (Self Employed)'] = SelfEmployedExpensesInvoice::docJson();

file_put_contents(__DIR__ . '/../docs/swagger/swagger.json', json_encode($docs, JSON_PRETTY_PRINT));
