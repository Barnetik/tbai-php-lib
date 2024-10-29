<?php

namespace Test\Barnetik\Tbai\LROE;

use Barnetik\Tbai\Api;
use Barnetik\Tbai\Api\Bizkaia\Endpoint;
use Barnetik\Tbai\LROE\Expenses\ExpensesInvoiceFactory;
use Barnetik\Tbai\LROE\Expenses\SelfEmployed\ExpensesWithoutInvoice;
use Barnetik\Tbai\PrivateKey;
use Barnetik\Tbai\TicketBai;
use DOMDocument;
use Test\Barnetik\TestCase;

class ExpensesTest extends TestCase
{
    const SUBMIT_RETRIES = 3;
    const SUBMIT_RETRY_DELAY = 3;
    const DEFAULT_TERRITORY = TicketBai::TERRITORY_BIZKAIA;

    // public function test_ticketbai_data_can_be_serialized(): void
    // {
    //     $expense = ExpensesInvoice::createFromJson(json_decode(file_get_contents(__DIR__ . '/../__files/expense-sample.json'), true));
    //     $expenseDocument = new DOMDocument('1.0', 'utf-8');
    //     $expenseDocument->appendChild($expense->xml($expenseDocument));
    //     echo $expenseDocument->saveXML();
    // }

    public function test_juridic_person_expenses_is_delivered(): void
    {
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];

        $privateKey = PrivateKey::p12($certFile);

        $expensesData = json_decode(file_get_contents(__DIR__ . '/../__files/juridic-person-expense-sample.json'), true);

        if ($expensesData['selfEmployed']) {
            $nif = $_ENV['TBAI_BIZKAIA_ISSUER_NIF_140'];
            $recipient = $_ENV['TBAI_BIZKAIA_ISSUER_NAME_140'];
        } else {
            $nif = $_ENV['TBAI_BIZKAIA_ISSUER_NIF_240'];
            $recipient = $_ENV['TBAI_BIZKAIA_ISSUER_NAME_240'];
        }

        $expensesData['recipient']['vatId'] = $nif;
        $expensesData['recipient']['name'] = $recipient;
        $expensesData['header']['invoiceNumber'] = time();
        sleep(1);
        $expenses = ExpensesInvoiceFactory::createFromJson($expensesData);
        
        $endpoint = new Endpoint(true, true);
        $response = $endpoint->submitExpensesInvoice($expenses, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());
        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $expenses->recipientVatId() . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "eus-bizkaia-n3-tipo-respuesta: " . $response->header('eus-bizkaia-n3-tipo-respuesta') . "\n";
            echo "eus-bizkaia-n3-identificativo: " . $response->header('eus-bizkaia-n3-identificativo') . "\n";
            echo "eus-bizkaia-n3-codigo-respuesta: " . $response->header('eus-bizkaia-n3-codigo-respuesta') . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Sent file: " . $endpoint->debugData(Api::DEBUG_SENT_FILE) . "\n";
            echo "Response file: " . $responseFile . "\n";
            print_r($response->errorDataRegistry());
        }

        $this->assertTrue($response->isCorrect());
    }

    public function test_self_employed_expenses_is_delivered(): void
    {
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];

        $privateKey = PrivateKey::p12($certFile);

        $expensesData = json_decode(file_get_contents(__DIR__ . '/../__files/self-employed-expense-sample.json'), true);

        if ($expensesData['selfEmployed']) {
            $nif = $_ENV['TBAI_BIZKAIA_ISSUER_NIF_140'];
            $recipient = $_ENV['TBAI_BIZKAIA_ISSUER_NAME_140'];
        } else {
            $nif = $_ENV['TBAI_BIZKAIA_ISSUER_NIF_240'];
            $recipient = $_ENV['TBAI_BIZKAIA_ISSUER_NAME_240'];
        }

        $expensesData['recipient']['vatId'] = $nif;
        $expensesData['recipient']['name'] = $recipient;
        $expensesData['header']['invoiceNumber'] = time();
        sleep(1);
        $expenses = ExpensesInvoiceFactory::createFromJson($expensesData);
        
        $endpoint = new Endpoint(true, true);
        $response = $endpoint->submitExpensesInvoice($expenses, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());
        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $expenses->recipientVatId() . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "eus-bizkaia-n3-tipo-respuesta: " . $response->header('eus-bizkaia-n3-tipo-respuesta') . "\n";
            echo "eus-bizkaia-n3-identificativo: " . $response->header('eus-bizkaia-n3-identificativo') . "\n";
            echo "eus-bizkaia-n3-codigo-respuesta: " . $response->header('eus-bizkaia-n3-codigo-respuesta') . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Sent file: " . $endpoint->debugData(Api::DEBUG_SENT_FILE) . "\n";
            echo "Response file: " . $responseFile . "\n";
            print_r($response->errorDataRegistry());
        }

        $this->assertTrue($response->isCorrect());
    }


    public function test_self_employed_expenses_without_invoice_is_delivered(): void
    {
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];

        $privateKey = PrivateKey::p12($certFile);

        $expensesData = json_decode(file_get_contents(__DIR__ . '/../__files/self-employed-expense-without-invoice-sample.json'), true);

        $nif = $_ENV['TBAI_BIZKAIA_ISSUER_NIF_140'];
        $recipient = $_ENV['TBAI_BIZKAIA_ISSUER_NAME_140'];

        $expensesData['recipient']['vatId'] = $nif;
        $expensesData['recipient']['name'] = $recipient;
        $expensesData['header']['invoiceNumber'] = time();
        $expensesData['operationDate'] = date('d-m-Y'); // Only works onces a day, it will say that allready exists on other cases
        $expensesData['income']['irpfExpenseAmount'] = rand(1, 10000000);
        sleep(1);
        $expenses = ExpensesWithoutInvoice::createFromJson($expensesData);
        
        $endpoint = new Endpoint(true, true);
        $response = $endpoint->submitExpensesWithoutInvoice($expenses, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());
        if (!$response->isCorrect()) {
            if ($response->errorDataRegistry()[0]['errorMessage']['eu'] === 'Erregistro bikoiztua.') {
                $this->markTestSkipped('Duplicated registry. Already tested today.');
            }
            echo "\n";
            echo "VatId / IFZ / NIF: " . $expenses->recipientVatId() . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "eus-bizkaia-n3-tipo-respuesta: " . $response->header('eus-bizkaia-n3-tipo-respuesta') . "\n";
            echo "eus-bizkaia-n3-identificativo: " . $response->header('eus-bizkaia-n3-identificativo') . "\n";
            echo "eus-bizkaia-n3-codigo-respuesta: " . $response->header('eus-bizkaia-n3-codigo-respuesta') . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Sent file: " . $endpoint->debugData(Api::DEBUG_SENT_FILE) . "\n";
            echo "Response file: " . $responseFile . "\n";
            print_r($response->errorDataRegistry());
        }

        $this->assertTrue($response->isCorrect());
    }
}
