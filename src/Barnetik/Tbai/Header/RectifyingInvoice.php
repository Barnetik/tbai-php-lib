<?php

namespace Barnetik\Tbai\Header;

use Barnetik\Tbai\Exception\InvalidRectifyingInvoiceCode;
use Barnetik\Tbai\Exception\InvalidRectifyingInvoiceType;
use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Amount;
use DOMDocument;
use DOMNode;
use DOMXPath;

class RectifyingInvoice implements TbaiXml
{
    const CODE_R1 = 'R1';
    const CODE_R2 = 'R2';
    const CODE_R3 = 'R3';
    const CODE_R4 = 'R4';
    const CODE_R5 = 'R5';

    const TYPE_SUSTITUTION = 'S';
    const TYPE_DIFFERENCE = 'I';


    private string $code;
    private string $type;
    private ?RectifyingAmount $rectifyingAmount = null;

    public function __construct(string $code, string $type, RectifyingAmount $rectifyingAmount = null)
    {
        $this->setCode($code);
        $this->setType($type);
        if ($rectifyingAmount) {
            $this->rectifyingAmount = $rectifyingAmount;
        }
    }

    private function setCode(string $code): self
    {
        if (!in_array($code, self::validCodes())) {
            throw new InvalidRectifyingInvoiceCode();
        }
        $this->code = $code;

        return $this;
    }

    private function setType(string $type): self
    {
        if (!in_array($type, self::validTypes())) {
            throw new InvalidRectifyingInvoiceType();
        }
        $this->type = $type;

        return $this;
    }


    private static function validCodes(): array
    {
        return [
        self::CODE_R1,
        self::CODE_R2,
        self::CODE_R3,
        self::CODE_R4,
        self::CODE_R5,
        ];
    }

    private static function validTypes(): array
    {
        return [
        self::TYPE_SUSTITUTION,
        self::TYPE_DIFFERENCE,
        ];
    }

    public static function createFromJson(array $jsonData): self
    {
        $code = $jsonData['code'];
        $type = $jsonData['type'];

        $rectifyingAmount = null;
        if (isset($jsonData['rectifyingAmount']) && $jsonData['rectifyingAmount']) {
            $rectifyingAmount = RectifyingAmount::createFromJson($jsonData['rectifyingAmount']);
        }

        $rectifyingInvoice = new RectifyingInvoice($code, $type, $rectifyingAmount);
        return $rectifyingInvoice;
    }

    public static function createFromXml(DOMXPath $xpath): self
    {
        $code = $xpath->evaluate('string(/T:TicketBai/Factura/CabeceraFactura/FacturaRectificativa/Codigo)');
        $type = $xpath->evaluate('string(/T:TicketBai/Factura/CabeceraFactura/FacturaRectificativa/Tipo)');

        $rectifyingAmount = null;
        if ($xpath->evaluate('boolean(/T:TicketBai/Factura/CabeceraFactura/FacturaRectificativa/ImporteRectificacionSustitutiva)')) {
            $rectifyingAmount = RectifyingAmount::createFromXml($xpath);
        }

        return new self($code, $type, $rectifyingAmount);
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $rectifyingInvoice = $domDocument->createElement('FacturaRectificativa');

        $rectifyingInvoice->appendChild(
            $domDocument->createElement('Codigo', $this->code)
        );

        $rectifyingInvoice->appendChild(
            $domDocument->createElement('Tipo', $this->type)
        );

        if ($this->rectifyingAmount) {
            $rectifyingInvoice->appendChild(
                $this->rectifyingAmount->xml($domDocument)
            );
        }

        return $rectifyingInvoice;
    }

    public static function docJson(): array
    {
        return [
        'type' => 'object',
        'properties' => [
        'code' => [
          'type' => 'string',
          'enum' => self::validCodes(),
          'description' => '
  Faktura zuzentzailearen mota identifikatzen duen kodea - Código que identifica el tipo de factura rectificativa
   * R1: Faktura zuzentzailea: zuzenbidean eta BEZaren Legearen 80.Bat, Bi eta Sei artikuluan oinarritutako akatsa - Factura rectificativa: error fundado en derecho y Art. 80 Uno, Dos y Seis de la Ley del IVA
   * R2: Faktura zuzentzailea: BEZaren Legearen 80.Hiru artikulua - Factura rectificativa: artículo 80 Tres de la Ley del IVA
   * R3: Faktura zuzentzailea: BEZaren Legearen 80.Lau artikulua - Factura rectificativa: artículo 80 Cuatro de la Ley del IVA
   * R4: Faktura zuzentzailea: gainerakoak - Factura rectificativa: Resto
   * R5: Faktura sinplifikatuak zuzentzeko faktura - Factura rectificativa en facturas simplificadas
          '
        ],
        'type' => [
          'type' => 'string',
          'enum' => self::validTypes(),
          'description' => '
Faktura zuzentzaile mota - Tipo de factura rectificativa
 * S: Ordezkapenagatiko faktura zuzentzailea - Factura rectificativa por sustitución
 * I: Diferentziengatiko faktura zuzentzailea - Factura rectificativa por diferencias
          ',
        ],
        'rectifyingAmount' => RectifyingAmount::docJson(),
        ],
        'required' => ['code', 'type']
        ];
    }

    public function toArray(): array
    {
        return [
        'code' => $this->code,
        'type' => $this->type,
        'rectifyingAmount' => $this->rectifyingAmount ? $this->rectifyingAmount->toArray() : null,
        ];
    }
}
