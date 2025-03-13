<?php

namespace Test\Barnetik\Tbai;

use Barnetik\Tbai\PrivateKey;
use Barnetik\Tbai\TicketBai;
use Barnetik\Tbai\ValueObject\VatId;
use DOMDocument;
use DOMXPath;
use Exception;
use lyquidity\xmldsig\XAdES;
use Test\Barnetik\TestCase;

class TicketBaiTest extends TestCase
{
    public function test_ticketbai_data_can_be_serialized(): void
    {
        $ticketbai = $this->getTicketBai();
        $this->assertIsString(json_encode($ticketbai->toArray()));
    }

    public function test_unsigned_TicketBai_validates_schema(): void
    {
        $ticketbai = $this->getTicketBai();
        $dom = $ticketbai->dom();
        $this->assertTrue($dom->schemaValidate(__DIR__ . '/__files/specs/ticketbaiv1-2-2-no-signature.xsd'));
    }

    public function test_ticketbai_can_be_generated_from_json(): void
    {
        $json = $this->getFilesContents('tbai-sample.json');
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createArabaVendor(), json_decode($json, true));
        $this->assertEquals(
            TicketBai::class,
            get_class($ticketbai)
        );

        $dom = $ticketbai->dom();
        $this->assertTrue($dom->schemaValidate(__DIR__ . '/__files/specs/ticketbaiv1-2-2-no-signature.xsd'));
    }

    public function test_ticketbai_can_be_generated_from_xml(): void
    {
        $certFile = $_ENV['TBAI_TEST_P12_PATH'];
        $certPassword = $_ENV['TBAI_TEST_P12_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';

        $ticketbai = $this->getTicketBai();
        $ticketbai->sign($privateKey, $certPassword, $filename);

        $signedDom = new DOMDocument();
        $signedDom->load($filename);

        $createdFromXmlSignedFile = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($createdFromXmlSignedFile, $createdFromXmlSignedFile . '.xml');
        $createdFromXmlSignedFile .= '.xml';

        $ticketbaiFromXml = TicketBai::createFromXml($signedDom->saveXML(), $ticketbai->territory(), false, $createdFromXmlSignedFile);

        $signedDom = new DOMDocument();
        $signedDom->loadXML($ticketbaiFromXml->signed());

        $this->assertTrue($signedDom->schemaValidate(__DIR__ . '/__files/specs/ticketbaiv1-2-2.xsd'));
    }

    public function test_TicketBai_can_be_signed_with_PFX_key(): void
    {
        $ticketbai = $this->getTicketBai();
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';

        $privateKey = PrivateKey::p12($_ENV['TBAI_TEST_P12_PATH']);
        $ticketbai->sign($privateKey, $_ENV['TBAI_TEST_P12_KEY'], $filename);
        $signedDom = new DOMDocument();
        $signedDom->load($filename);
        $this->assertTrue($signedDom->schemaValidate(__DIR__ . '/__files/specs/ticketbaiv1-2-2.xsd'));
    }

    public function test_TicketBai_can_be_signed_with_PEM_key(): void
    {
        $ticketbai = $this->getTicketBai();
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';

        $privateKey = PrivateKey::pem($_ENV['TBAI_TEST_PEM_CRT_PATH'], $_ENV['TBAI_TEST_PEM_KEY_PATH']);
        $ticketbai->sign($privateKey, $_ENV['TBAI_TEST_PEM_PASSWORD'], $filename);
        $signedDom = new DOMDocument();
        $signedDom->load($filename);
        $this->assertTrue($signedDom->schemaValidate(__DIR__ . '/__files/specs/ticketbaiv1-2-2.xsd'));
    }

    public function test_TicketBai_can_be_signed_with_singlefile_PEM(): void
    {
        $ticketbai = $this->getTicketBai();
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';

        $privateKey = PrivateKey::pem($_ENV['TBAI_TEST_SINGLE_PEM_PATH'], $_ENV['TBAI_TEST_SINGLE_PEM_PATH']);
        $ticketbai->sign($privateKey, $_ENV['TBAI_TEST_PEM_PASSWORD'], $filename);
        $signedDom = new DOMDocument();
        $signedDom->load($filename);
        $this->assertTrue($signedDom->schemaValidate(__DIR__ . '/__files/specs/ticketbaiv1-2-2.xsd'));
    }

    public function test_TicketBai_can_have_multiple_vats(): void
    {
        $nif = $_ENV['TBAI_ARABA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_ARABA_ISSUER_NAME'];
        $license = $_ENV['TBAI_ARABA_APP_LICENSE'];
        $developer = $_ENV['TBAI_ARABA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_ARABA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_ARABA_APP_VERSION'];

        $ticketbai = $this->ticketBaiMother->createTicketBaiMultiVat($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA);
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';

        $privateKey = PrivateKey::p12($_ENV['TBAI_TEST_P12_PATH']);
        $ticketbai->sign($privateKey, $_ENV['TBAI_TEST_P12_KEY'], $filename);
        $signedDom = new DOMDocument();
        $signedDom->load($filename);
        $this->assertTrue($signedDom->schemaValidate(__DIR__ . '/__files/specs/ticketbaiv1-2-2.xsd'));
    }

    public function test_TicketBai_signed_file_is_valid(): void
    {
        $ticketbai = $this->getTicketBai();
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';
        $privateKey = PrivateKey::p12($_ENV['TBAI_TEST_P12_PATH']);
        $ticketbai->sign($privateKey, $_ENV['TBAI_TEST_P12_KEY'], $filename);
        $signedDom = new DOMDocument();
        $signedDom->load($filename);

        try {
            ob_start();
            XAdES::verifyDocument(
                $filename
            );
            ob_end_clean();
            $this->assertTrue(true);
        } catch (Exception $e) {
            var_dump($e->getFile());
            var_dump($e->getLine());
            $this->fail($e->getMessage());
        }
    }


    public function test_TicketBai_generates_signature_values(): void
    {
        $ticketbai = $this->getTicketBai();
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';
        $privateKey = PrivateKey::p12($_ENV['TBAI_TEST_P12_PATH']);
        $ticketbai->sign($privateKey, $_ENV['TBAI_TEST_P12_KEY'], $filename);
        
        $this->assertGreaterThan(100, mb_strlen($ticketbai->signatureValue()));
        $this->assertEquals(100, mb_strlen($ticketbai->chainSignatureValue()));
        $this->assertEquals(13, mb_strlen($ticketbai->shortSignatureValue()));
    }

    public function test_TicketBai_without_lines_validates_schema(): void
    {

        $nif = $_ENV['TBAI_ARABA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_ARABA_ISSUER_NAME'];
        $license = $_ENV['TBAI_ARABA_APP_LICENSE'];
        $developer = $_ENV['TBAI_ARABA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_ARABA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_ARABA_APP_VERSION'];

        $ticketbai = $this->ticketBaiMother->createEmptyTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA);

        $dom = $ticketbai->dom();
        $this->assertTrue($dom->schemaValidate(__DIR__ . '/__files/specs/ticketbaiv1-2-2-no-signature.xsd'));
    }

    private function getTicketBai(): TicketBai
    {
        $nif = $_ENV['TBAI_ARABA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_ARABA_ISSUER_NAME'];
        $license = $_ENV['TBAI_ARABA_APP_LICENSE'];
        $developer = $_ENV['TBAI_ARABA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_ARABA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_ARABA_APP_VERSION'];

        return $this->ticketBaiMother->createTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA);
    }

    public function test_TicketBai_issuer_name_allows_ampersands(): void
    {
        $nif = $_ENV['TBAI_ARABA_ISSUER_NIF'];
        $issuer = 'Test with & on issuer name';
        $license = $_ENV['TBAI_ARABA_APP_LICENSE'];
        $developer = $_ENV['TBAI_ARABA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_ARABA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_ARABA_APP_VERSION'];

        $ticketbai = $this->ticketBaiMother->createTicketBaiMultiVat($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA);
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';

        $privateKey = PrivateKey::p12($_ENV['TBAI_TEST_P12_PATH']);
        $ticketbai->sign($privateKey, $_ENV['TBAI_TEST_P12_KEY'], $filename);
        $signedDom = new DOMDocument();
        $signedDom->load($filename);
        $this->assertTrue($signedDom->schemaValidate(__DIR__ . '/__files/specs/ticketbaiv1-2-2.xsd'));
    }

    public function test_gh29_TicketBai_sends_operation_date_element(): void
    {
        $nif = $_ENV['TBAI_ARABA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_ARABA_ISSUER_NAME'];
        $license = $_ENV['TBAI_ARABA_APP_LICENSE'];
        $developer = $_ENV['TBAI_ARABA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_ARABA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_ARABA_APP_VERSION'];

        $ticketbai = $this->ticketBaiMother->createTicketBaiWithOperationDate($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA);
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';

        $privateKey = PrivateKey::p12($_ENV['TBAI_TEST_P12_PATH']);
        $ticketbai->sign($privateKey, $_ENV['TBAI_TEST_P12_KEY'], $filename);
        $signedDom = new DOMDocument();
        $signedDom->load($filename);
        $xpath = new DOMXPath($signedDom);
        $operationDateValue = $xpath->evaluate('string(/T:TicketBai/Factura/DatosFactura/FechaOperacion)');
        $this->assertFalse(empty($operationDateValue));
    }

    public function test_gh47_foreign_recipient_ticketbai_can_be_generated_from_xml(): void
    {
        $nif = $_ENV['TBAI_ARABA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_ARABA_ISSUER_NAME'];
        $license = $_ENV['TBAI_ARABA_APP_LICENSE'];
        $developer = $_ENV['TBAI_ARABA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_ARABA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_ARABA_APP_VERSION'];

        $certFile = $_ENV['TBAI_TEST_P12_PATH'];
        $certPassword = $_ENV['TBAI_TEST_P12_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';

        $ticketbai = $this->ticketBaiMother->createTicketBaiWithForeignServices($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA, false, VatId::VAT_ID_TYPE_NIF, '00000000T', 'IE');
        $ticketbai->sign($privateKey, $certPassword, $filename);

        $signedDom = new DOMDocument();
        $signedDom->load($filename);

        $createdFromXmlSignedFile = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($createdFromXmlSignedFile, $createdFromXmlSignedFile . '.xml');
        $createdFromXmlSignedFile .= '.xml';

        $ticketbaiFromXml = TicketBai::createFromXml($signedDom->saveXML(), $ticketbai->territory(), false, $createdFromXmlSignedFile);
        $this->assertEquals('IE', $ticketbaiFromXml->toArray()['subject']['recipients'][0]['countryCode']);
        $xml = new DOMDocument('1.0', 'utf-8');
        $ticketbaiFromXml->xml($xml);
        $xpath = new DOMXPath($xml);

        $finalVatId = $xpath->evaluate('string(/T:TicketBai/Sujetos/Destinatarios/IDDestinatario/IDOtro/CodigoPais)');
        $this->assertEquals('IE', $finalVatId);


        $ticketbai = $this->getTicketBai();
        $ticketbai->sign($privateKey, $certPassword, $filename);

        $signedDom = new DOMDocument();
        $signedDom->load($filename);

        $createdFromXmlSignedFile = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($createdFromXmlSignedFile, $createdFromXmlSignedFile . '.xml');
        $createdFromXmlSignedFile .= '.xml';

        $ticketbaiFromXml = TicketBai::createFromXml($signedDom->saveXML(), $ticketbai->territory(), false, $createdFromXmlSignedFile);

        $xml = new DOMDocument('1.0', 'utf-8');
        $ticketbaiFromXml->xml($xml);
        $xpath = new DOMXPath($xml);

        $finalVatId = $xpath->evaluate('string(/T:TicketBai/Sujetos/Destinatarios/IDDestinatario/NIF)');
        $this->assertEquals('00000000T', $finalVatId);
    }


    public function test_gh48_Greek_Vatid_prefix_EL_instead_of_GR(): void
    {
        $nif = $_ENV['TBAI_ARABA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_ARABA_ISSUER_NAME'];
        $license = $_ENV['TBAI_ARABA_APP_LICENSE'];
        $developer = $_ENV['TBAI_ARABA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_ARABA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_ARABA_APP_VERSION'];

        $expectedVatId = 'EL00000000T';

        $ticketbai = $this->ticketBaiMother->createTicketBaiWithForeignServices($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA, false, VatId::VAT_ID_TYPE_NIF, '00000000T', 'GR');
        $xml = new DOMDocument('1.0', 'utf-8');
        $ticketbai->xml($xml);
        $xpath = new DOMXPath($xml);

        $finalVatId = $xpath->evaluate('string(/T:TicketBai/Sujetos/Destinatarios/IDDestinatario/IDOtro/ID)');
        $this->assertEquals($expectedVatId, $finalVatId);

        $ticketbai = $this->ticketBaiMother->createTicketBaiWithForeignServices($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA, false, VatId::VAT_ID_TYPE_NIF, 'EL00000000T', 'GR');
        $xml = new DOMDocument('1.0', 'utf-8');
        $ticketbai->xml($xml);
        $xpath = new DOMXPath($xml);

        $finalVatId = $xpath->evaluate('string(/T:TicketBai/Sujetos/Destinatarios/IDDestinatario/IDOtro/ID)');
        $this->assertEquals($expectedVatId, $finalVatId);

        // Check other countries still work correctly
        $ticketbai = $this->ticketBaiMother->createTicketBaiWithForeignServices($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA, false, VatId::VAT_ID_TYPE_NIF, '00000000T', 'IE');
        $xml = new DOMDocument('1.0', 'utf-8');
        $ticketbai->xml($xml);
        $xpath = new DOMXPath($xml);

        $finalVatId = $xpath->evaluate('string(/T:TicketBai/Sujetos/Destinatarios/IDDestinatario/IDOtro/ID)');
        $this->assertEquals('IE00000000T', $finalVatId);
    }

    public function test_gh55_TicketBai_should_allow_return_of_null_batuzIncomeTaxCollection(): void
    {
        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBai();
        $this->assertNull($ticketbai->batuzIncomeTaxes());
    }


}
