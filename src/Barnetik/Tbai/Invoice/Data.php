<?php

namespace Barnetik\Tbai\Invoice;

use Barnetik\Tbai\Exception\InvalidVatRegimeException;
use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\Invoice\Data\Detail;
use Barnetik\Tbai\ValueObject\Amount;
use DOMDocument;
use DOMNode;
use DOMXPath;
use InvalidArgumentException;
use OutOfBoundsException;

class Data implements TbaiXml
{
    const VAT_REGIME_01 = '01';
    const VAT_REGIME_02 = '02';
    const VAT_REGIME_03 = '03';
    const VAT_REGIME_04 = '04';
    const VAT_REGIME_05 = '05';
    const VAT_REGIME_06 = '06';
    const VAT_REGIME_07 = '07';
    const VAT_REGIME_08 = '08';
    const VAT_REGIME_09 = '09';
    const VAT_REGIME_10 = '10';
    const VAT_REGIME_11 = '11';
    const VAT_REGIME_12 = '12';
    const VAT_REGIME_13 = '13';
    const VAT_REGIME_14 = '14';
    const VAT_REGIME_15 = '15';
    const VAT_REGIME_51 = '51';
    const VAT_REGIME_52 = '52';
    const VAT_REGIME_53 = '53';

    private string $description;
    private Amount $total;
    private ?Amount $supportedRetention = null;
    private ?Amount $taxBaseCost = null;
    private array $vatRegimes = [];
    private array $details = [];

    public function __construct(string $description, Amount $total, array $vatRegimes, ?Amount $supportedRetention = null, ?Amount $taxBaseCost = null)
    {
        $this->description = $description;
        $this->total = $total;
        $this->setVatRegimes($vatRegimes);

        if ($supportedRetention) {
            $this->supportedRetention = $supportedRetention;
        }

        if ($taxBaseCost) {
            $this->taxBaseCost = $taxBaseCost;
        }
    }

    private function setVatRegimes(array $vatRegimes): self
    {
        if (!sizeof($vatRegimes)) {
            throw new InvalidArgumentException('Empty vat regimes is not allowed');
        }

        foreach ($vatRegimes as $vatRegime) {
            $this->addVatRegime($vatRegime);
        }

        return $this;
    }

    public function addVatRegime(string $vatRegime): self
    {
        if (!in_array($vatRegime, self::validVatRegimes())) {
            throw new InvalidVatRegimeException();
        }

        if (!sizeof($this->vatRegimes) < 2) {
            throw new OutOfBoundsException('Too many subject and not exempt breakdown items');
        }

        $this->vatRegimes[] = $vatRegime;
        return $this;
    }

    public function addDetail(Detail $detail): self
    {
        array_push($this->details, $detail);
        return $this;
    }

    private static function validVatRegimes(): array
    {
        return [
            self::VAT_REGIME_01,
            self::VAT_REGIME_02,
            self::VAT_REGIME_03,
            self::VAT_REGIME_04,
            self::VAT_REGIME_05,
            self::VAT_REGIME_06,
            self::VAT_REGIME_07,
            self::VAT_REGIME_08,
            self::VAT_REGIME_09,
            self::VAT_REGIME_10,
            self::VAT_REGIME_11,
            self::VAT_REGIME_12,
            self::VAT_REGIME_13,
            self::VAT_REGIME_14,
            self::VAT_REGIME_15,
            self::VAT_REGIME_51,
            self::VAT_REGIME_52,
            self::VAT_REGIME_53,
        ];
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $data = $domDocument->createElement('DatosFactura');
        $data->appendChild(
            $domDocument->createElement('DescripcionFactura',
                htmlspecialchars($this->description, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8')
            )
        );

        if (sizeof($this->details)) {
            $details = $domDocument->createElement('DetallesFactura');
            foreach ($this->details as $detail) {
                $details->appendChild($detail->xml($domDocument));
            }
            $data->appendChild($details);
        }

        $data->appendChild(
            $domDocument->createElement('ImporteTotalFactura', $this->total)
        );

        if ($this->supportedRetention) {
            $data->appendChild(
                $domDocument->createElement('RetencionSoportada', $this->supportedRetention)
            );
        }

        if ($this->taxBaseCost) {
            $data->appendChild(
                $domDocument->createElement('BaseImponibleACoste', $this->taxBaseCost)
            );
        }

        $vatRegimeKeys = $domDocument->createElement('Claves');
        foreach ($this->vatRegimes as $vatRegime) {
            $keyId = $domDocument->createElement('IDClave');
            $keyId->appendChild(
                $domDocument->createElement('ClaveRegimenIvaOpTrascendencia', $vatRegime)
            );

            $vatRegimeKeys->appendChild($keyId);
        }
        $data->appendChild($vatRegimeKeys);
        return $data;
    }

    public function total(): Amount
    {
        return $this->total;
    }

    public static function createFromXml(DOMXPath $xpath): self
    {
        $description = $xpath->evaluate('string(/T:TicketBai/Factura/DatosFactura/DescripcionFactura)');
        $total = new Amount($xpath->evaluate('string(/T:TicketBai/Factura/DatosFactura/ImporteTotalFactura)'));

        $vatRegimes = [];
        foreach ($xpath->query('/T:TicketBai/Factura/DatosFactura/Claves/IDClave') as $node) {
            $vatRegimes[] = $xpath->evaluate('string(ClaveRegimenIvaOpTrascendencia)', $node);
        }

        $supportedRetention = null;
        $supportedRetentionValue = $xpath->evaluate('string(/T:TicketBai/Factura/DatosFactura/RetencionSoportada)');
        if ($supportedRetentionValue) {
            $supportedRetention = new Amount($supportedRetentionValue);
        }

        $taxBaseCost = null;
        $taxBaseCostValue = $xpath->evaluate('string(/T:TicketBai/Factura/DatosFactura/BaseImponibleACoste)');
        if ($taxBaseCostValue) {
            $taxBaseCost = new Amount($taxBaseCostValue);
        }

        $invoiceData = new Data($description, $total, $vatRegimes, $supportedRetention, $taxBaseCost);

        foreach ($xpath->query('/T:TicketBai/Factura/DatosFactura/DetallesFactura/IDDetalleFactura') as $node) {
            $detail = Detail::createFromXml($xpath, $node);
            $invoiceData->addDetail($detail);
        }

        return $invoiceData;
    }

    public static function createFromJson(array $jsonData): self
    {
        $description = $jsonData['description'];
        $total = new Amount($jsonData['total']);
        $vatRegimes = $jsonData['vatRegimes'];

        $supportedRetention = null;
        $taxBaseCost = null;

        if (isset($jsonData['supportedRetention']) && $jsonData['supportedRetention'] !== '') {
            $supportedRetention = new Amount($jsonData['supportedRetention']);
        }

        if (isset($jsonData['taxBaseCost']) && $jsonData['taxBaseCost'] !== '') {
            $taxBaseCost = new Amount($jsonData['taxBaseCost']);
        }

        $invoiceData = new Data($description, $total, $vatRegimes, $supportedRetention, $taxBaseCost);

        foreach ($jsonData['details'] as $jsonDetail) {
            $detail = Detail::createFromJson($jsonDetail);
            $invoiceData->addDetail($detail);
        }

        return $invoiceData;
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'description' => [
                    'type' => 'string',
                    'maxLength' => 250,
                ],
                'details' => [
                    'type' => 'array',
                    'items' => Detail::docJson(),
                    'minItems' => 0,
                    'maxItems' => 1000,
                ],
                'total' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Zenbatekoa guztira (2 dezimalekin) - Importe total (2 decimales)'
                ],
                'vatRegimes' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                        'enum' => self::validVatRegimes(),
                        'description' => '
 * 01: Erregimen orokorreko eragiketa eta hurrengo balioetan jaso gabe dagoen beste edozein kasu - Operación de régimen general y cualquier otro supuesto que no esté recogido en los siguientes valores
 * 02: Esportazioa - Exportación
 * 03: Erabilitako ondasunen, arte objektuen, zaharkinen eta bilduma objektuen araudi berezia aplikatzen zaien eragiketak - Operaciones a las que se aplique el régimen especial de bienes usados, objetos de arte, antigüedades y objetos de colección
 * 04: Inbertsio urrearen araubide berezia - Régimen especial del oro de inversión
 * 05: Bidaia-agentzien araubide berezia - Régimen especial de las agencias de viajes
 * 06: BEZeko erakundeen multzoaren araudi berezia (maila aurreratua) - Régimen especial grupo de entidades en IVA (Nivel Avanzado)
 * 07: Kutxa-irizpidearen araubide berezia - Régimen especial del criterio de caja
 * 08: Ekoizpen, Zerbitzu eta Inportazioaren gaineko Zergari / Kanarietako Zeharkako Zerga Orokorrari lotutako eragiketak - Operaciones sujetas al IPSI/IGIC (Impuesto sobre la Producción, los Servicios y la Importación / Impuesto General Indirecto Canario)
 * 09: Besteren izenean eta kontura ari diren bidai agentziek egindako zerbitzuen fakturazioa(Fakturazio Araudiko 3. xedapen gehigarria) - Facturación de las prestaciones de servicios de agencias de viaje que actúan como mediadoras en nombre y por cuenta ajena (disposición adicional 3ª del Reglamento de Facturación)
 * 10: Hirugarrenen kontura kobratzea ordainsari profesionalak edo jabetza industrialetik eratorritako eskubideak, egilearenak edo bazkideen, bazkideen edo elkargokideen kontura kobratzeko eginkizun horiek betetzen dituzten sozietate, elkarte, elkargo profesional edo bestelako erakundeek egindakoak - Cobros por cuenta de terceros o terceras de honorarios profesionales o de derechos derivados de la propiedad industrial, de autor u otros por cuenta de sus socios, socias, asociados, asociadas, colegiados o colegiadas efectuados por sociedades, asociaciones, colegios profesionales u otras entidades que realicen estas funciones de cobro
 * 11: Negozio lokala errentatzeko eragiketak, atxikipenari lotuak - Operaciones de arrendamiento de local de negocio sujetos a retención
 * 12: Negozio lokala errentatzeko eragiketak, atxikipenari lotu gabeak - Operaciones de arrendamiento de local de negocio no sujetos a retención
 * 13: Negozio lokala errentatzeko eragiketak, atxikipenari lotuak eta lotu gabeak - Operaciones de arrendamiento de local de negocio sujetas y no sujetas a retención
 * 14: Hartzailea administrazio publiko bat denean ordaintzeke dauden BEZdun fakturak, obra ziurtagirietakoak - Factura con IVA pendiente de devengo en certificaciones de obra cuyo destinatario sea una Administración Pública
 * 15: Segidako traktuko eragiketetan ordaintzeke dagoen BEZdun faktura - Factura con IVA pendiente de devengo en operaciones de tracto sucesivo
 * 51: Baliokidetasun errekarguko eragiketak - Operaciones en recargo de equivalencia
 * 52: Erregimen erraztuko eragiketak - Operaciones en régimen simplificado
 * 53: BEZaren ondorioetarako enpresari edo profesionaltzat jotzen ez diren pertsona edo erakundeek egindako eragiketak - Operaciones realizadas por personas o entidades que no tengan la consideración de empresarios, empresarias o profesionales a efectos del IVA
                        '
                    ],
                    'minItems' => 1,
                    'maxItems' => 3,
                    'description' => 'Gakoak: BEZaren araubideen eta zerga-ondorioak dauzkaten eragiketak - Claves de regímenes de IVA y operaciones con trascendencia tributaria'
                ],
                'supportedRetention' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Jasandako atxikipena (2 dezimalekin) - Retención soportada (2 decimales)'
                ],
                'taxBaseCost' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Kosturako zerga-oinarria (2 dezimalekin) - Base imponible a coste (2 decimales)'
                ]
            ],
            'required' => ['description', 'total', 'vatRegimes']
        ];
    }

    public function toArray(): array
    {
        return [
            'description' => $this->description,
            'details' => array_map(function ($detail) {
                return $detail->toArray();
            }, $this->details),
            'total' => (string)$this->total,
            'vatRegimes' => $this->vatRegimes,
            'supportedRetention' => $this->supportedRetention ? (string)$this->supportedRetention : null,
            'taxBaseCost' => $this->taxBaseCost ? (string)$this->taxBaseCost : null,
        ];
    }
}
// element name="FechaOperacion" type="T:FechaType" minOccurs="0"/>
